<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanyProfileResource;
use App\Models\CompanyProfile;
use App\Http\Requests\StoreCompanyProfileRequest;
use App\Http\Requests\UpdateCompanyProfileRequest;

class CompanyProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getSetting()
    {
        $companyProfile = CompanyProfile::first();
        return response([
            'message' => 'Get Company Profile Success',
            'data' => new CompanyProfileResource($companyProfile),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateSetting(UpdateCompanyProfileRequest $request)
    {
        $data = $request->validated();
        $companyProfile = CompanyProfile::first();
        $companyProfile->update($data);
        return response([
            'message' => 'Update Company Profile Success',
            'data' => new CompanyProfileResource($companyProfile),
        ]);
    }

    public function updateLogo()
    {
        $companyProfile = CompanyProfile::first();
        $data = request()->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if (request()->hasFile('logo')) {
            $data['logo'] = request()->file('logo')->store('company_profile', 'public');
        }
        $companyProfile->update($data);
        return response([
            'message' => 'Update Logo Success',
            'data' => [
                'logo' => $companyProfile->logo,
            ],
        ]);
    }

    public function updateBanner()
    {
        $companyProfile = CompanyProfile::first();
        $data = request()->validate([
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if (request()->hasFile('banner')) {
            $data['banner'] = request()->file('banner')->store('company_profile', 'public');
        }
        $companyProfile->update($data);
        return response([
            'message' => 'Update Banner Success',
            'data' => [
                'banner' => $companyProfile->banner,
            ],
        ]);
    }

    public function updateSlider()
    {
        $companyProfile = CompanyProfile::first();
        $data = request()->validate([
            'slider' => 'required|array',
            'slider.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        foreach ($data['slider'] as $key => $value) {
            if (request()->hasFile('slider.' . $key)) {
                $data['slider'][$key] = request()->file('slider.' . $key)->store('company_profile', 'public');
            }
        }
        $companyProfile->update($data);
        return response([
            'message' => 'Update Slider Success',
            'data' => [
                'slider' => $companyProfile->only(['slider_1', 'slider_2', 'slider_3']),
            ],
        ]);
    }
}
