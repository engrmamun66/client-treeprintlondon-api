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
            $brands = Quotation::orderBy('id', 'DESC')->paginate($perPage);

            // Return paginated response
            return $this->sendResponse($brands, 'Quotation list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            // Find the brand by ID
            $quotation = Quotation::find($id);

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

}
