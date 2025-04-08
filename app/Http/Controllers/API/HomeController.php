<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Traits\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Mail;
use App\Mail\ContactFormSubmitEmailToAdmin; 
use App\Mail\ContactFormSubmitEmailToCustomer; 

class HomeController extends BaseController
{
    use FileUpload;

    public function submitContactForm(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'note' => 'required|string',
                'files.*' => [
                    'nullable',
                    'file',
                    'mimes:jpeg,png,jpg,gif,pdf,doc,docx', // Allowed file types
                    'max:10000', // Maximum file size: 2MB
                ], // Each file max 10MB
            ]);
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
