<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <i class="ph ph-scan-face text-neon text-2xl"></i>
            <span>Pendaftaran Wajah Member</span>
        </div>
    </x-slot>

    <div class="mb-6 rounded-xl border {{ $apiReady ? 'border-green-500/30 bg-green-500/5' : 'border-amber-500/30 bg-amber-500/5' }} p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <i class="ph {{ $apiReady ? 'ph-check-circle text-green-400' : 'ph-warning-circle text-amber-400' }} mt-0.5 text-xl"></i>
                <div>
                    <p class="{{ $apiReady ? 'text-green-300' : 'text-amber-300' }} font-semibold">
                        {{ $apiReady ? 'API biometrik YuNet/SFace siap' : 'API biometrik belum siap' }}
                    </p>
                    <p class="mt-1 text-sm text-gray-400">
                        @if($apiReady)
                            Sampel diproses oleh service biometrik lokal (bisagym-api). Foto mentah tidak disimpan oleh Laravel.
                        @else
                            Jalankan service biometrik lokal (bisagym-api), lalu isi BISAGYM_FACE_API_EMAIL dan BISAGYM_FACE_API_PASSWORD pada .env Laravel.
                        @endif
                    </p>
                </div>
            </div>
            @unless($apiReady)
                <a href="{{ route('face-enrollment.index', request()->query()) }}" class="rounded-lg border border-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-300 transition hover:border-neon hover:text-neon">
                    Periksa lagi
                </a>
            @endunless
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="space-y-4 xl:col-span-1">
            <div class="rounded-xl border border-gray-800 bg-card p-5 shadow-lg">
                <div class="mb-4 flex items-center justify-between border-b border-gray-800 pb-3">
                    <div>
                        <h3 class="font-semibold text-white">Pilih member</h3>
                        <p class="mt-1 text-xs text-gray-500">Cari berdasarkan nama atau VIP ID.</p>
                    </div>
                    <i class="ph ph-users text-xl text-neon"></i>
                </div>

                <form method="GET" action="{{ route('face-enrollment.index') }}" class="mb-4 flex gap-2">
                    <label class="sr-only" for="member-search">Cari member</label>
                    <input id="member-search" name="search" value="{{ $search }}" type="search" placeholder="Nama atau VIP ID..."
                        class="min-w-0 flex-1 rounded-lg border border-gray-700 bg-dark px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-neon focus:ring-neon">
                    <button class="rounded-lg bg-neon px-3 text-darker transition hover:bg-[#c4e600]" aria-label="Cari member">
                        <i class="ph ph-magnifying-glass text-lg"></i>
                    </button>
                </form>

                <div class="space-y-2">
                    @forelse($members as $member)
                        <a href="{{ route('face-enrollment.index', array_filter(['search' => $search, 'member_id' => $member->id])) }}"
                            class="flex items-center justify-between gap-3 rounded-lg border px-3 py-3 transition {{ $selectedMember?->id === $member->id ? 'border-neon/70 bg-neon/10' : 'border-gray-800 bg-dark hover:border-gray-600' }}">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-gray-700 bg-gray-800 font-semibold text-neon">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($member->name, 0, 1)) }}
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-white">{{ $member->name }}</span>
                                    <span class="block text-xs font-mono text-gray-500">{{ $member->member_id }}</span>
                                </span>
                            </div>
                            @if($member->face_enrollment_status === 'ENROLLED')
                                <span class="shrink-0 rounded-full border border-green-500/30 bg-green-500/10 px-2 py-1 text-[10px] font-semibold text-green-400">Terdaftar</span>
                            @elseif($member->face_enrollment_status === 'IN_PROGRESS')
                                <span class="shrink-0 rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-1 text-[10px] font-semibold text-amber-400">Proses</span>
                            @else
                                <span class="shrink-0 rounded-full border border-gray-700 bg-gray-800 px-2 py-1 text-[10px] font-semibold text-gray-400">Belum</span>
                            @endif
                        </a>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-700 px-4 py-8 text-center text-sm text-gray-500">
                            Member tidak ditemukan.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">{{ $members->links() }}</div>
            </div>

            <div class="rounded-xl border border-gray-800 bg-card p-5">
                <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-white">
                    <i class="ph ph-shield-check text-lg text-neon"></i> Alur dan privasi
                </h3>
                <ul class="space-y-2 text-xs leading-relaxed text-gray-400">
                    <li class="flex gap-2"><i class="ph ph-check text-neon"></i><span>Persetujuan eksplisit member diperlukan sebelum pemrosesan biometrik.</span></li>
                    <li class="flex gap-2"><i class="ph ph-check text-neon"></i><span>YuNet mendeteksi wajah dan SFace membentuk template dari pose depan, kiri, dan kanan.</span></li>
                    <li class="flex gap-2"><i class="ph ph-check text-neon"></i><span>Foto hanya dikirim untuk diproses; Laravel tidak menyimpan gambar mentah.</span></li>
                    <li class="flex gap-2"><i class="ph ph-info text-amber-400"></i><span>SFace RGB bukan pemindai 3D dan tidak menyediakan liveness detection dengan konfigurasi default.</span></li>
                </ul>
            </div>
        </section>

        <section class="xl:col-span-2">
            @if($selectedMember)
                <div id="face-enrollment-workflow"
                    data-member-id="{{ $selectedMember->id }}"
                    data-start-url="{{ route('face-enrollment.start', $selectedMember) }}"
                    data-sample-url="{{ route('face-enrollment.sample', $selectedMember) }}"
                    data-complete-url="{{ route('face-enrollment.complete', $selectedMember) }}"
                    data-revoke-url="{{ route('face-enrollment.revoke', $selectedMember) }}"
                    class="rounded-xl border border-gray-800 bg-card p-5 shadow-lg md:p-7">
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-gray-800 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-neon/30 bg-neon/10 text-lg font-bold text-neon">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($selectedMember->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-gray-500">Member terpilih</p>
                                <h2 class="mt-0.5 text-lg font-bold text-white">{{ $selectedMember->name }}</h2>
                                <p class="font-mono text-xs text-neon">{{ $selectedMember->member_id }}</p>
                            </div>
                        </div>
                        @if($selectedMember->face_enrollment_status === 'ENROLLED')
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full border border-green-500/30 bg-green-500/10 px-3 py-1.5 text-xs font-semibold text-green-400">
                                    <i class="ph ph-check-circle"></i> Profil wajah aktif
                                </span>
                                <button id="revoke-profile" type="button" class="rounded-lg border border-red-500/40 px-3 py-1.5 text-xs font-semibold text-red-300 transition hover:bg-red-500/10">
                                    Cabut persetujuan
                                </button>
                            </div>
                        @endif
                    </div>

                    @if($selectedMember->face_enrolled_at)
                        <p class="-mt-3 mb-5 text-xs text-gray-500">Pendaftaran terakhir: {{ $selectedMember->face_enrolled_at->format('d M Y, H:i') }}</p>
                    @endif

                    <div id="enrollment-alert" class="mb-4 hidden rounded-lg border px-4 py-3 text-sm" role="alert"></div>

                    <div id="consent-panel" class="space-y-5">
                        <div>
                            <div class="mb-3 flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-neon/10 text-xs font-bold text-neon">01</span>
                                <h3 class="font-semibold text-white">Persetujuan biometrik</h3>
                            </div>
                            <div class="rounded-lg border border-gray-700 bg-dark p-4 text-sm leading-relaxed text-gray-400">
                                <p class="font-semibold text-white">Penggunaan data wajah di BISA Gym</p>
                                <p class="mt-2">Template wajah digunakan untuk proses verifikasi akses gym. Sistem menganalisis gambar kamera RGB dengan YuNet dan SFace. Template disimpan di layanan biometrik sampai persetujuan dicabut; gambar mentah tidak disimpan oleh Laravel maupun layanan enrollment.</p>
                                <p class="mt-2 text-xs text-gray-500">Liveness detection tidak aktif pada konfigurasi standar. Member dapat menolak dan tetap menggunakan proses check-in non-biometrik.</p>
                            </div>
                            <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-lg border border-gray-800 p-3 transition hover:border-gray-600">
                                <input id="consent-checkbox" type="checkbox" class="mt-1 rounded border-gray-600 bg-dark text-neon focus:ring-neon">
                                <span class="text-sm">
                                    <span class="block font-medium text-white">Member menyetujui pemrosesan data wajah</span>
                                    <span class="mt-1 block text-xs text-gray-500">Persetujuan diberikan secara sadar setelah tujuan, retensi, hak pencabutan, dan alternatif dijelaskan.</span>
                                </span>
                            </label>
                        </div>

                        <button id="start-enrollment" type="button" {{ $apiReady ? '' : 'disabled' }}
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-neon px-4 py-3 font-bold text-darker transition hover:bg-[#c4e600] disabled:cursor-not-allowed disabled:opacity-40">
                            <i class="ph ph-camera text-xl"></i> Mulai pendaftaran wajah
                        </button>
                    </div>

                    <div id="capture-panel" class="hidden space-y-5">
                        <div>
                            <div class="mb-3 flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-neon/10 text-xs font-bold text-neon">02</span>
                                <h3 class="font-semibold text-white">Ambil sampel wajah</h3>
                            </div>
                            <div class="mb-4 grid grid-cols-3 gap-2" id="pose-progress"></div>
                            
                            <div class="relative overflow-hidden rounded-xl border border-gray-700 bg-black">
                                <video id="face-camera" class="aspect-video w-full object-cover" autoplay playsinline muted></video>
                                
                                <!-- Face Positioning Guide Overlay -->
                                <div id="face-guide-overlay" class="pointer-events-none absolute inset-0 z-30 flex items-center justify-center" style="z-index: 30;">
                                    <div class="relative flex flex-col items-center justify-between py-6" style="width: 210px; height: 270px; border: 2px dashed rgba(224, 255, 0, 0.75); border-radius: 48% 48% 44% 44%; box-shadow: 0 0 30px rgba(224, 255, 0, 0.25); background: rgba(0, 0, 0, 0.1);">
                                        <!-- 4 Corner Brackets -->
                                        <span style="position: absolute; top: -3px; left: -3px; width: 24px; height: 24px; border-top: 4px solid #e0ff00; border-left: 4px solid #e0ff00; border-top-left-radius: 10px;"></span>
                                        <span style="position: absolute; top: -3px; right: -3px; width: 24px; height: 24px; border-top: 4px solid #e0ff00; border-right: 4px solid #e0ff00; border-top-right-radius: 10px;"></span>
                                        <span style="position: absolute; bottom: -3px; left: -3px; width: 24px; height: 24px; border-bottom: 4px solid #e0ff00; border-left: 4px solid #e0ff00; border-bottom-left-radius: 10px;"></span>
                                        <span style="position: absolute; bottom: -3px; right: -3px; width: 24px; height: 24px; border-bottom: 4px solid #e0ff00; border-right: 4px solid #e0ff00; border-bottom-right-radius: 10px;"></span>

                                        <!-- Badges inside guide -->
                                        <span class="rounded bg-black/70 px-2 py-0.5 text-[11px] font-semibold tracking-wider text-neon uppercase">Area Wajah</span>
                                        <span class="rounded bg-black/70 px-2 py-0.5 text-[10px] text-gray-300">Posisikan wajah di sini</span>
                                    </div>
                                </div>

                                <div id="camera-placeholder" class="hidden aspect-video flex-col items-center justify-center gap-2 text-center text-sm text-gray-400">
                                    <i class="ph ph-camera-slash text-3xl text-amber-400"></i>
                                    <span>Kamera tidak dapat dibuka. Izinkan kamera dan gunakan localhost atau HTTPS.</span>
                                </div>
                            </div>

                            <div class="mt-4 rounded-lg border border-gray-800 bg-dark p-4 text-center">
                                <p class="text-xs uppercase tracking-wider text-gray-500">Instruksi pose</p>
                                <h4 id="pose-title" class="mt-1 text-lg font-bold text-neon"></h4>
                                <p id="pose-instruction" class="mt-1 text-sm text-gray-400"></p>
                            </div>
                        </div>
                        <button id="capture-sample" type="button" class="flex w-full items-center justify-center gap-2 rounded-lg bg-neon px-4 py-3 font-bold text-darker transition hover:bg-[#c4e600] disabled:cursor-not-allowed disabled:opacity-40">
                            <i class="ph ph-aperture text-xl"></i> Ambil sampel pose
                        </button>
                    </div>

                    <div id="success-panel" class="hidden py-8 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-green-500/30 bg-green-500/10 text-3xl text-green-400">
                            <i class="ph ph-check-circle"></i>
                        </div>
                        <h3 class="mt-4 text-xl font-bold text-white">Pendaftaran wajah berhasil</h3>
                        <p class="mt-2 text-sm text-gray-400">Template biometrik aktif pada layanan biometrik. Gambar mentah tidak disimpan.</p>
                    </div>
                </div>
            @else
                <div class="flex min-h-[360px] flex-col items-center justify-center rounded-xl border border-dashed border-gray-700 bg-card p-8 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full border border-gray-700 bg-dark text-3xl text-gray-500">
                        <i class="ph ph-scan-face"></i>
                    </div>
                    <h2 class="mt-4 text-lg font-semibold text-white">Pilih member untuk memulai</h2>
                    <p class="mt-2 max-w-sm text-sm text-gray-500">Pilih member dari daftar di samping. Pastikan member memberikan persetujuan sebelum mengambil sampel wajah.</p>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>

@if($selectedMember)
<script>
(() => {
    const root = document.getElementById('face-enrollment-workflow');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const alertBox = document.getElementById('enrollment-alert');
    const consentPanel = document.getElementById('consent-panel');
    const capturePanel = document.getElementById('capture-panel');
    const successPanel = document.getElementById('success-panel');
    const video = document.getElementById('face-camera');
    const placeholder = document.getElementById('camera-placeholder');
    const startButton = document.getElementById('start-enrollment');
    const captureButton = document.getElementById('capture-sample');
    const revokeButton = document.getElementById('revoke-profile');
    const poses = {
        FRONT: { title: 'Hadap depan', instruction: 'Posisikan wajah di dalam bingkai, lihat lurus ke kamera, dan pertahankan posisi.' },
        LEFT: { title: 'Sedikit ke kiri', instruction: 'Putar kepala perlahan sedikit ke arah kiri Anda.' },
        RIGHT: { title: 'Sedikit ke kanan', instruction: 'Putar kepala perlahan sedikit ke arah kanan Anda.' },
    };
    let stream;
    let sessionId;
    let requiredPoses = [];
    let poseIndex = 0;
    let acceptedPoses = [];
    let samplesComplete = false;
    let busy = false;

    function showAlert(message, success = false) {
        alertBox.textContent = message;
        alertBox.className = `mb-4 rounded-lg border px-4 py-3 text-sm ${success ? 'border-green-500/40 bg-green-500/10 text-green-300' : 'border-red-500/40 bg-red-500/10 text-red-300'}`;
        alertBox.classList.remove('hidden');
    }

    function hideAlert() {
        alertBox.classList.add('hidden');
    }

    function faceErrorMessage(error) {
        const code = ((error && error.code) || '').toUpperCase();
        if (['NO_FACE', 'NO_FACE_DETECTED', 'FACE_NOT_DETECTED'].includes(code)) {
            return 'Wajah tidak terdeteksi di kamera. Posisikan wajah Anda tepat di dalam bingkai panduan, pastikan pencahayaan cukup dan wajah tidak terhalang.';
        }
        if (['MULTIPLE_FACES', 'MULTIPLE_FACES_DETECTED', 'TOO_MANY_FACES'].includes(code)) {
            return 'Terdeteksi lebih dari satu wajah. Pastikan hanya ada satu orang di depan kamera.';
        }
        if (['FACE_TOO_SMALL'].includes(code)) {
            return 'Wajah terlalu jauh dari kamera. Silakan mendekat hingga wajah memenuhi bingkai panduan.';
        }
        if (['LOW_QUALITY', 'LOW_IMAGE_QUALITY', 'IMAGE_QUALITY_TOO_LOW', 'QUALITY_TOO_LOW', 'IMAGE_TOO_BLURRY'].includes(code)) {
            return 'Kualitas foto belum memenuhi standar (kurang fokus atau buram). Jaga kepala dan kamera tetap stabil.';
        }
        if (['BAD_EXPOSURE'].includes(code)) {
            return 'Pencahayaan wajah kurang optimal (terlalu gelap atau terlalu silau). Hindari lampu tepat di belakang kepala (backlight).';
        }
        if (['INCONSISTENT_ENROLLMENT_SAMPLES'].includes(code)) {
            return 'Sampel pose wajah tidak konsisten (terlihat seperti orang berbeda). Pastikan orang yang sama pada setiap pose lalu ulangi.';
        }
        if (['INVALID_IMAGE', 'IMAGE_DECODE_FAILED', 'CAMERA_CAPTURE_FAILED'].includes(code)) {
            return 'Gambar kamera tidak valid atau gagal diproses. Pastikan kamera siap lalu ambil ulang.';
        }
        return error?.message || 'Sampel tidak dapat diproses. Sesuaikan posisi dan coba lagi.';
    }

    function stopCamera() {
        stream?.getTracks().forEach((track) => track.stop());
        stream = undefined;
        video.srcObject = null;
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            const err = new Error(result.message || 'Permintaan tidak dapat diproses.');
            err.code = result.code;
            throw err;
        }
        return result;
    }

    async function postImage(pose, image) {
        const url = new URL(root.dataset.sampleUrl, window.location.origin);
        url.searchParams.set('session_id', sessionId);
        url.searchParams.set('pose_type', pose);
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'image/jpeg', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: image,
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            const err = new Error(result.message || 'Sampel wajah ditolak.');
            err.code = result.code;
            throw err;
        }
        return result;
    }

    function renderProgress() {
        const progress = document.getElementById('pose-progress');
        progress.replaceChildren(...requiredPoses.map((pose, index) => {
            const item = document.createElement('div');
            const done = acceptedPoses.includes(pose);
            const current = index === poseIndex && !samplesComplete;
            item.className = `rounded-lg border p-2 text-center text-xs ${done ? 'border-green-500/40 bg-green-500/10 text-green-300' : current ? 'border-neon/50 bg-neon/10 text-neon' : 'border-gray-800 bg-dark text-gray-500'}`;
            item.textContent = `${String(index + 1).padStart(2, '0')} · ${poses[pose]?.title || pose}`;
            return item;
        }));
    }

    function updatePose() {
        const pose = requiredPoses[poseIndex];
        if (!pose) return;
        document.getElementById('pose-title').textContent = poses[pose]?.title || pose;
        document.getElementById('pose-instruction').textContent = poses[pose]?.instruction || 'Ikuti posisi yang diminta.';
        captureButton.innerHTML = `<i class="ph ph-aperture text-xl"></i> Ambil sampel ${poses[pose]?.title || pose}`;
        renderProgress();
    }

    async function openCamera() {
        if (!navigator.mediaDevices?.getUserMedia) {
            placeholder.classList.remove('hidden');
            placeholder.classList.add('flex');
            video.classList.add('hidden');
            throw new Error('Browser tidak mendukung kamera. Gunakan localhost atau HTTPS.');
        }
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 1920 }, height: { ideal: 1080 } },
                audio: false,
            });
            video.srcObject = stream;
            await video.play();
            await new Promise((resolve, reject) => {
                if (video.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA) return resolve();
                const timer = window.setTimeout(() => reject(new Error('Kamera belum menghasilkan gambar.')), 8000);
                video.addEventListener('loadeddata', () => {
                    window.clearTimeout(timer);
                    resolve();
                }, { once: true });
            });
        } catch (error) {
            stopCamera();
            placeholder.classList.remove('hidden');
            placeholder.classList.add('flex');
            video.classList.add('hidden');
            throw error instanceof Error ? error : new Error('Akses kamera gagal.');
        }
    }

    async function captureImage() {
        if (!stream || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA || !video.videoWidth) {
            throw new Error('Kamera belum siap. Tunggu gambar kamera muncul lalu coba lagi.');
        }
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const context = canvas.getContext('2d');
        if (!context) throw new Error('Gambar kamera tidak dapat diproses.');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.95));
        if (!(blob instanceof Blob) || !blob.size) throw new Error('Gambar kamera kosong. Coba ambil ulang.');
        if (blob.size > 5000000) throw new Error('Ukuran gambar melebihi 5 MB. Atur kamera ke resolusi lebih rendah.');
        return blob;
    }

    startButton.addEventListener('click', async () => {
        hideAlert();
        if (!document.getElementById('consent-checkbox').checked) {
            showAlert('Persetujuan member harus dicentang sebelum memulai.');
            return;
        }
        startButton.disabled = true;
        try {
            const session = await postJson(root.dataset.startUrl, { consent: true });
            sessionId = session.session_id;
            requiredPoses = session.required_poses;
            if (!requiredPoses.length || requiredPoses.some((pose) => !poses[pose])) {
                throw new Error('API mengirim daftar pose yang tidak didukung. Periksa konfigurasi enrollment.');
            }
            consentPanel.classList.add('hidden');
            capturePanel.classList.remove('hidden');
            updatePose();
            await openCamera();
        } catch (error) {
            showAlert(error.message || 'Pendaftaran tidak dapat dimulai.');
            startButton.disabled = false;
        }
    });

    revokeButton?.addEventListener('click', async () => {
        if (!window.confirm('Cabut persetujuan biometrik dan nonaktifkan template wajah member ini?')) return;
        revokeButton.disabled = true;
        try {
            await postJson(root.dataset.revokeUrl, {});
            window.location.reload();
        } catch (error) {
            showAlert(error.message || 'Persetujuan tidak dapat dicabut.');
            revokeButton.disabled = false;
        }
    });

    captureButton.addEventListener('click', async () => {
        if (busy) return;
        hideAlert();
        busy = true;
        captureButton.disabled = true;
        const pose = requiredPoses[poseIndex];
        try {
            if (samplesComplete) {
                const result = await postJson(root.dataset.completeUrl, {});
                capturePanel.classList.add('hidden');
                successPanel.classList.remove('hidden');
                stopCamera();
                showAlert(`Profil aktif dengan model ${result.model.name} ${result.model.version}.`, true);
                return;
            }

            // Brief wait for camera auto-focus to settle
            await new Promise(r => setTimeout(r, 200));

            const result = await postImage(pose, await captureImage());
            acceptedPoses.push(pose);
            if (poseIndex < requiredPoses.length - 1) {
                poseIndex += 1;
                showAlert(`Pose diterima oleh ${result.engine}. Kualitas sampel: ${Math.round(result.quality_score * 100)}%.`, true);
                updatePose();
            } else {
                samplesComplete = true;
                document.getElementById('pose-title').textContent = 'Sampel lengkap';
                document.getElementById('pose-instruction').textContent = 'Selesaikan pembuatan template wajah.';
                captureButton.innerHTML = '<i class="ph ph-check-circle text-xl"></i> Aktifkan profil wajah';
                renderProgress();
                showAlert('Semua pose diterima. Lanjutkan untuk membuat template biometrik.', true);
            }
        } catch (error) {
            showAlert(faceErrorMessage(error));



        } finally {
            busy = false;
            captureButton.disabled = false;
        }
    });

    window.addEventListener('beforeunload', stopCamera, { once: true });
})();
</script>
@endif
