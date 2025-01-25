<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Quotation;
use App\Models\QuotationFile;
use App\Http\Requests\QuotationRequest;
use App\Traits\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Mail;
use App\Mail\QuotationEmailToAdmin; 
use App\Mail\QuotationEmailToCustomer; 

class QuotationController extends BaseController
{
    use FileUpload;

    public function index(Request $request)
    {
        try {
            // Get the 'perPage' parameter from the request, default to 15
            $perPage = $request->per_page ?? 15;

            // Fetch paginated brands
            $quotations = Quotation::with('files')->orderBy('id', 'DESC')->paginate($perPage);

            // Return paginated response
            return $this->sendResponse($quotations, 'Quotation list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            // Find the brand by ID
            $quotation = Quotation::with('files')->find($id);

            // Check if the brand exists
            if (!$quotation) {
                return $this->sendError('Quotation not found.', [], 404);
            }

            // Return the brand data
            return $this->sendResponse($quotation, 'Quotation found.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function store(QuotationRequest $request)
    {
        try {
            $validatedData = $request->validated();
            // Create the quotation
            $quotation = new Quotation();
            $quotation->type_of_service = $validatedData['type_of_service'];
            $quotation->delivery_date = $validatedData['delivery_date'] ?? null;
            $quotation->full_name = $validatedData['full_name'];
            $quotation->email = $validatedData['email'];
            $quotation->phone = $validatedData['phone'];
            $quotation->requirements = $validatedData['requirements'] ?? null;
            $quotation->save();
    
            // Handle file uploads if files are provided
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $filePath = $this->FileUpload($file, 'quotation'); // Call your custom upload function
                    QuotationFile::create([
                        'file' => $filePath,
                        'quotation_id' => $quotation->id
                    ]);
                }
            }
            $mailData = $quotation;
            Mail::to('rabbimahmud95@gmail.com')->send(new QuotationEmailToAdmin($mailData));
            Mail::to($mailData->email)->send(new QuotationEmailToCustomer($mailData));
    
            return $this->sendResponse($quotation, 'Quotation created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
    public function update(Request $request, Quotation $quotation)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|numeric|min:1|max:4',
            ]);
           $quotation->status = $request->status;
           $quotation->save();
            // Return the brand data
            return $this->sendResponse($quotation, 'Status updated successfully.', 200);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Find the quotation by ID
            $quotation = Quotation::with('files')->find($id);

            // Check if the quotation exists
            if (!$quotation) {
                return $this->sendError('Quotation not found.', [], 404);
            }

            // Delete associated files
            foreach ($quotation->files as $file) {
                if (Storage::exists($file->file)) {
                    Storage::delete($file->file);
                }
                $file->delete();
            }

            // Delete the quotation
            $quotation->delete();

            return $this->sendResponse([], 'Quotation deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
    public function downloadFile($fileId)
    {
        $file = QuotationFile::find($fileId);

        // Check if the file exists in the database and storage
        if (!$file || !Storage::disk('public')->exists($file->file)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        // Return the file as a download response
        return Storage::disk('public')->download($file->file);
    }




}
