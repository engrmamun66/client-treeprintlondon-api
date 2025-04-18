<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Traits\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Mail;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Quotation;
use App\Mail\ContactFormSubmitEmailToAdmin; 
use App\Mail\ContactFormSubmitEmailToCustomer; 
use Illuminate\Support\Facades\Validator;

class HomeController extends BaseController
{
    use FileUpload;

    public function dashBoardData(){
        try {
            $totalBrands = Brand::count();
            $totalOrders = Order::count();
            $totalQuotations = Quotation::count();
            $totalProducts = Product::count();
            $completedOrders = Order::where('payment_status', 'completed')->count();
            $recentOrders = Order::whereDate('created_at', '>=', now()->subDays(7))->count();
            $recentQuotations = Quotation::whereDate('created_at', '>=', now()->subDays(7))->count();
            $data = [
                'totalBrands' => $totalBrands,
                'totalOrders' => $totalOrders,
                'totalQuotations' => $totalQuotations,
                'totalProducts' => $totalProducts,
                'completedOrders' => $completedOrders,
                'recentOrders' => $recentOrders,
                'recentQuotations' => $recentQuotations,
            ];
            return $this->sendResponse($data, 'Data retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function submitContactForm(Request $request)
    {
        try {
            // $this->validate($request, [
            //     'name' => 'required|string|max:255',
            //     'email' => 'required|email|max:255',
            //     'phone' => 'nullable|string|max:20',
            //     'note' => 'nullable|string',
            //     'files.*' => [
            //         'nullable',
            //         'file',
            //         'mimes:jpeg,png,jpg,gif,pdf,doc,docx', // Allowed file types
            //         'max:10000', // Maximum file size: 2MB
            //     ], // Each file max 10MB
            // ]);
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'note' => 'nullable|string',
                'files.*' => [
                    'nullable',
                    'file',
                    'mimes:jpeg,png,jpg,gif,pdf,doc,docx', // Allowed file types
                    'max:10000', // Maximum file size: 2MB
                ], // Each file max 10MB
            ]);

            if ($validator->fails()) {
                return $this->sendError("Validation Error", $validator->errors(), 422);
            }

            // Get the validated data
            $validated = $validator->validated();

           // Handle file uploads
           $filePaths = [];
           if ($request->hasFile('files')) {
               foreach ($request->file('files') as $file) {
                   $uploadedPath = $this->FileUpload($file, 'quotation'); 
                   if ($uploadedPath) {
                       $filePaths[] = $uploadedPath;
                   }
               }
           }

           // Send emails
           Mail::to('support@teeprintlondon.co.uk')
               ->send(new ContactFormSubmitEmailToAdmin($validated, $filePaths));
               
           Mail::to($validated['email'])
               ->send(new ContactFormSubmitEmailToCustomer($validated, $filePaths));
            return $this->sendResponse([], 'Thank you for your message! We will get back to you soon.', 200);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

}
