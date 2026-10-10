@extends('layouts.app')

@section('title', 'Visual Product Search - Search by Image - ShopKart')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4 text-center">
                <div class="mb-3 text-primary">
                    <i class="bi bi-camera display-3"></i>
                </div>
                <h3 class="fw-bold mb-2">Search by Photo / Image</h3>
                <p class="text-muted">Take a photo or upload an image of shoes, watches, ethnic kurtas or electronics to find matching products in our database.</p>

                <form action="{{ route('visual.search') }}" method="POST" enctype="multipart/form-data" class="my-4">
                    @csrf
                    <div class="sk-dropzone mb-3" onclick="document.getElementById('imageUploadInput').click()">
                        <i class="bi bi-cloud-arrow-up text-warning display-4 mb-2"></i>
                        <h6 class="fw-bold mb-1">Click to browse or drag & drop image here</h6>
                        <small class="text-muted">Supports JPG, PNG, WEBP (Max 5MB)</small>
                        <input type="file" name="image" id="imageUploadInput" class="d-none" accept="image/*" required onchange="previewImage(this)">
                    </div>

                    <div id="imagePreviewContainer" class="mb-3" style="display: none;">
                        <img id="imagePreview" src="#" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                    </div>

                    <button type="submit" id="submitSearchBtn" class="btn btn-sk-primary w-100 py-2 fw-bold" style="display: none;">
                        <i class="bi bi-search me-1"></i> Scan & Find Similar Products
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreviewContainer').style.display = 'block';
                document.getElementById('submitSearchBtn').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
