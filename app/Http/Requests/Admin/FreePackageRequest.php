<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FreePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'destination'     => ['required', 'string', 'max:100'],
            'points_required' => ['required', 'integer', 'min:1', 'max:1000000'],
            'duration_days'   => ['nullable', 'integer', 'min:1', 'max:365'],
            'description'     => ['nullable', 'string', 'max:2000'],
            'image'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // 2 MB
            'valid_until'     => ['nullable', 'date', 'after:today'],
            'display_order'   => ['nullable', 'integer', 'min:0'],
            'is_active'       => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'اسم الباكج مطلوب.',
            'destination.required'     => 'الوجهة مطلوبة.',
            'points_required.required' => 'عدد النقاط المطلوبة.',
            'points_required.min'      => 'النقاط يجب أن تكون رقمًا موجبًا.',
            'image.image'              => 'الملف يجب أن يكون صورة.',
            'image.max'                => 'حجم الصورة الأقصى 2 ميجابايت.',
            'valid_until.after'        => 'تاريخ الانتهاء يجب أن يكون في المستقبل.',
        ];
    }
}
