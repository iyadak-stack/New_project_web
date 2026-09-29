<head>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <h3 class="text-center mb-4">
                        รีวิวติวเตอร์
                    </h3>

                    <form action="{{ route('reviews.store') }}" method="POST">
                        @csrf

                        {{-- Temporary test data --}}
                        <input
                            type="hidden"
                            name="Appointment_Appointment_id"
                            value="AP00000001"
                        >

                        <input
                            type="hidden"
                            name="Tutor_profiles_tutor_id"
                            value="TP00000001"
                        >

                        {{-- Rating --}}
                        <div class="mb-4">
                            <label class="form-label">
                                ให้คะแนนติวเตอร์
                            </label>

                            <div class="d-flex gap-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="rating"
                                            value="{{ $i }}"
                                            id="rating{{ $i }}"
                                            required
                                        >

                                        <label
                                            class="form-check-label"
                                            for="rating{{ $i }}"
                                        >
                                            {{ $i }}
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        {{-- Comment --}}
                        <div class="mb-4">
                            <label
                                for="comment"
                                class="form-label"
                            >
                                Comment
                            </label>

                            <textarea
                                class="form-control"
                                id="comment"
                                name="Comment"
                                rows="6"
                                placeholder="แสดงความคิดเห็นเกี่ยวกับการเรียน..."
                                required
                            ></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                ยืนยัน
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>