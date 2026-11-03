<x-app-layout>
    <x-slot name="header">Streaming Kamera</x-slot>

    <style>
        .stream-eyebrow{font-size:.65rem;font-weight:700;letter-spacing:.12em;color:#a3e635;margin-bottom:.25rem}
        .stream-title{font-size:1.35rem;font-weight:700;color:#fff;margin-bottom:.15rem}
        .stream-desc{font-size:.78rem;color:#9ca3af;margin-bottom:1.2rem}

        .stream-control-panel{display:flex;align-items:center;gap:1.2rem;padding:1rem 1.2rem;border-radius:.5rem;background:#1a1d1a;border:1px solid #2d302d;margin-bottom:1rem}
        .stream-control-copy{display:flex;align-items:center;gap:.75rem;margin-right:auto}
        .stream-control-icon{width:2.5rem;height:2.5rem;display:grid;place-items:center;border-radius:.5rem;color:#6b7280;background:#262926;font-size:1.25rem}
        .stream-control-icon.active{color:#65a30d;background:#1a2e05}
        .stream-control-copy .info{display:flex;flex-direction:column;gap:.1rem}
        .stream-control-copy .info strong{font-size:.8rem;color:#e5e7eb}
        .stream-control-copy .info p{margin:0;color:#6b7280;font-size:.7rem}
        .stream-branch{display:flex;align-items:center;gap:.5rem;font-size:.72rem;color:#9ca3af}
        .stream-branch select{min-width:160px;height:2rem;font-size:.72rem;background:#111311;border:1px solid #3f3f46;border-radius:.35rem;color:#e5e7eb;padding:0 .5rem}
        .stream-toggle-control{display:flex;align-items:center;gap:.6rem;padding-left:1rem;border-left:1px solid #2d302d;font-size:.72rem;font-weight:700;color:#d1d5db}

        .switch{position:relative;width:2.2rem;height:1.15rem;flex:0 0 auto}
        .switch input{position:absolute;opacity:0}
        .switch>span{display:block;width:100%;height:100%;border-radius:.65rem;background:#3f3f46;transition:background .2s;cursor:pointer}
        .switch>span::after{content:'';position:absolute;left:2px;top:2px;width:.85rem;height:.85rem;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.4);transition:transform .2s}
        .switch input:checked+span{background:#65a30d}
        .switch input:checked+span::after{transform:translateX(1rem)}

        .stream-layout{display:grid;grid-template-columns:1fr 16rem;gap:1rem;align-items:start}
        @media(max-width:768px){.stream-layout{grid-template-columns:1fr}}

        .stream-camera-panel{overflow:hidden;border-radius:.5rem;background:#111311;border:1px solid #2d302d}
        .stream-camera-stage{position:relative;width:100%;overflow:hidden;background:#0a0c0a;transition:aspect-ratio .2s ease}
        .stream-camera-stage video{width:100%;height:100%;display:block;object-fit:cover;transform:scaleX(-1)}
        .stream-placeholder{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;padding:2rem;color:#fff;background:#0d0f0d;text-align:center}
        .stream-placeholder .placeholder-icon{font-size:2.5rem;color:#a3e635;margin-bottom:.25rem}
        .stream-placeholder strong{font-size:.9rem}
        .stream-placeholder span{max-width:26rem;color:#6b7280;font-size:.7rem;line-height:1.5}
        .stream-placeholder button{margin-top:.3rem;padding:.35rem .75rem;border-radius:.35rem;background:#262926;border:1px solid #3f3f46;color:#d1d5db;font-size:.7rem;cursor:pointer}
        .stream-placeholder button:hover{background:#333633}

        .stream-live-badge{position:absolute;right:.8rem;top:.8rem;display:flex;align-items:center;gap:.35rem;padding:.35rem .55rem;border-radius:.25rem;color:#fff;background:rgba(0,0,0,.78);font-size:.55rem;font-weight:800;letter-spacing:.12em}
        .stream-live-badge .dot{width:.45rem;height:.45rem;border-radius:50%;background:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.18)}
        .stream-live-badge .spinner{display:inline-block;width:.7rem;height:.7rem;border:2px solid rgba(255,255,255,.2);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}

        .stream-face-box{position:absolute;z-index:2;min-width:2.2rem;min-height:2.2rem;border:3px solid;border-radius:5px;filter:drop-shadow(0 2px 5px rgba(0,0,0,.3));transition:left .42s cubic-bezier(.2,.7,.2,1),top .42s cubic-bezier(.2,.7,.2,1),width .42s cubic-bezier(.2,.7,.2,1),height .42s cubic-bezier(.2,.7,.2,1),opacity .2s ease}
        .stream-face-box.stale{opacity:.48}
        .stream-face-box.registered{border-color:#a3e635;box-shadow:inset 0 0 0 1px rgba(15,22,10,.25)}
        .stream-face-box.unregistered{border-color:#f87171;box-shadow:inset 0 0 0 1px rgba(25,10,8,.25)}
        .stream-face-box .face-label{position:absolute;left:-3px;top:-1.9rem;min-width:max-content;display:flex;align-items:center;gap:.3rem;padding:.35rem .5rem;border-radius:.25rem .25rem .25rem 0;color:#fff;font-size:.6rem;font-weight:800}
        .stream-face-box.registered .face-label{background:#26301f}
        .stream-face-box.unregistered .face-label{background:#7f1d1d}
        .stream-face-box .face-label i{font-size:.85rem}
        .stream-face-box .face-label small{color:rgba(255,255,255,.7);font-size:.5rem;font-weight:500}

        .stream-error{margin:.75rem;padding:.6rem .75rem;border-radius:.35rem;background:#451a1a;color:#fca5a5;font-size:.72rem;border:1px solid #7f1d1d}

        .stream-info-panel{display:flex;flex-direction:column;gap:.85rem}
        .stream-summary{padding:1rem;border-radius:.5rem;background:#1a1d1a;border:1px solid #2d302d}
        .stream-summary .kicker{font-size:.55rem;font-weight:700;letter-spacing:.1em;color:#6b7280;margin-bottom:.5rem}
        .stream-summary .row{display:flex;align-items:baseline;justify-content:space-between;padding:.75rem 0;border-bottom:1px solid #2d302d}
        .stream-summary .row:last-child{border:0;padding-bottom:0}
        .stream-summary .row strong{font-size:1.3rem;color:#fff}
        .stream-summary .row span{color:#6b7280;font-size:.6rem}

        .stream-legend{padding:1rem;border-radius:.5rem;background:#1a1d1a;border:1px solid #2d302d}
        .stream-legend h3{margin:0 0 .75rem;font-size:.8rem;color:#e5e7eb}
        .stream-legend .item{display:flex;align-items:flex-start;gap:.5rem;margin-top:.6rem}
        .stream-legend .dot-box{width:.6rem;height:.6rem;flex:0 0 auto;margin-top:.15rem;border:2px solid;border-radius:2px}
        .stream-legend .dot-box.reg{border-color:#65a30d}
        .stream-legend .dot-box.unreg{border-color:#dc2626}
        .stream-legend .item span{display:flex;flex-direction:column;color:#6b7280;font-size:.6rem;line-height:1.45}
        .stream-legend .item strong{color:#d1d5db;font-size:.68rem}

        .stream-privacy{display:flex;gap:.6rem;padding:.85rem;border-radius:.5rem;border:1px solid #365314;background:#1a2e05;color:#65a30d}
        .stream-privacy i{font-size:1.2rem;flex:0 0 auto}
        .stream-privacy strong{font-size:.68rem;display:block}
        .stream-privacy p{margin:.25rem 0 0;color:#6b7280;font-size:.58rem;line-height:1.5}

        @keyframes spin{to{transform:rotate(360deg)}}
    </style>

    <div>
        <p class="stream-eyebrow">BIOMETRIK REAL-TIME</p>
        <h1 class="stream-title">Streaming Kamera</h1>
        <p class="stream-desc">Deteksi beberapa wajah secara langsung untuk melihat apakah profilnya sudah terdaftar. Frame diproses sesaat dan tidak disimpan.</p>
    </div>

    @if(! $apiReady)
        <div style="padding:2rem;text-align:center;color:#f87171;background:#1a1d1a;border:1px solid #7f1d1d;border-radius:.5rem">
            <i class="ph ph-warning-circle" style="font-size:2.5rem;display:block;margin-bottom:.5rem"></i>
            <strong style="font-size:.9rem">API Biometrik Tidak Tersedia</strong>
            <p style="color:#9ca3af;font-size:.72rem;margin-top:.35rem">Pastikan service biometrik (bisagym-api) berjalan di port 8001 dan konfigurasi .env sudah benar.</p>
        </div>
    @else
        <div class="stream-control-panel">
            <div class="stream-control-copy">
                <div class="stream-control-icon" id="streamIcon">
                    <i class="ph ph-video-camera"></i>
                </div>
                <div class="info">
                    <strong id="streamLabel">Streaming nonaktif</strong>
                    <p>Aktifkan hanya saat petugas sedang melakukan verifikasi wajah.</p>
                </div>
            </div>

            @if(count($branches) > 1)
                <label class="stream-branch">
                    Cabang
                    <select id="branchSelect">
                        @foreach($branches as $branch)
                            @if($branch['is_active'] ?? true)
                                <option value="{{ $branch['id'] }}" {{ ($branch['id'] ?? '') === $branchId ? 'selected' : '' }}>{{ $branch['name'] ?? $branch['code'] ?? 'Cabang' }}</option>
                            @endif
                        @endforeach
                    </select>
                </label>
            @endif

            <div class="stream-toggle-control">
                <span>Aktifkan Streaming</span>
                <label class="switch">
                    <input type="checkbox" id="streamToggle">
                    <span></span>
                </label>
            </div>
        </div>

        <div class="stream-layout">
            <section class="stream-camera-panel">
                <div class="stream-camera-stage" id="cameraStage" style="aspect-ratio:16/9">
                    <video id="cameraVideo" muted playsinline></video>

                    <div class="stream-placeholder" id="placeholderOff">
                        <i class="ph ph-video-camera placeholder-icon"></i>
                        <strong>Streaming belum diaktifkan</strong>
                        <span>Tekan tombol Aktifkan Streaming untuk mulai mendeteksi wajah.</span>
                    </div>

                    <div class="stream-placeholder" id="placeholderRequesting" style="display:none">
                        <div class="spinner" style="width:2rem;height:2rem;border:3px solid rgba(255,255,255,.2);border-top-color:#a3e635;border-radius:50%;animation:spin .6s linear infinite"></div>
                        <strong>Menyiapkan kamera...</strong>
                    </div>

                    <div class="stream-placeholder" id="placeholderDenied" style="display:none">
                        <i class="ph ph-warning placeholder-icon" style="color:#f87171"></i>
                        <strong>Izin kamera diblokir</strong>
                        <span>Izinkan kamera melalui pengaturan situs browser.</span>
                        <button onclick="retryCamera()"><i class="ph ph-arrow-clockwise"></i> Coba lagi</button>
                    </div>

                    <div class="stream-placeholder" id="placeholderError" style="display:none">
                        <i class="ph ph-warning placeholder-icon" style="color:#f87171"></i>
                        <strong>Kamera tidak tersedia</strong>
                        <span>Periksa webcam lalu coba kembali.</span>
                        <button onclick="retryCamera()"><i class="ph ph-arrow-clockwise"></i> Coba lagi</button>
                    </div>

                    <div id="faceBoxContainer"></div>

                    <div class="stream-live-badge" id="liveBadge" style="display:none">
                        <span class="dot"></span> LIVE
                        <span class="spinner" id="liveSpinner" style="display:none"></span>
                    </div>
                </div>

                <div class="stream-error" id="streamError" style="display:none"></div>
            </section>

            <aside class="stream-info-panel">
                <section class="stream-summary">
                    <span class="kicker">HASIL STREAMING</span>
                    <div class="row"><strong id="totalFaces">0</strong><span>Wajah terlacak</span></div>
                    <div class="row"><strong id="registeredFaces">0</strong><span>Sudah terdaftar</span></div>
                    <div class="row"><strong id="unregisteredFaces">0</strong><span>Belum dikenali</span></div>
                </section>

                <section class="stream-legend">
                    <h3>Keterangan</h3>
                    <div class="item"><div class="dot-box reg"></div><span><strong>Terdaftar</strong>Profil wajah cocok dengan database cabang.</span></div>
                    <div class="item"><div class="dot-box unreg"></div><span><strong>Belum terdaftar</strong>Belum ada profil yang cocok atau gambar kurang jelas.</span></div>
                </section>

                <section class="stream-privacy">
                    <i class="ph ph-shield-check"></i>
                    <div>
                        <strong>Pemrosesan lokal dan sementara</strong>
                        <p>Gambar tidak disimpan. Fitur ini bukan liveness detection dan tidak memberikan keputusan akses.</p>
                    </div>
                </section>
            </aside>
        </div>
    @endif

    <canvas id="captureCanvas" style="display:none"></canvas>

    @if($apiReady)
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const POLL_DELAY_MS = 550;
        const BOX_SMOOTHING = 0.58;
        const MAX_MISSED_FRAMES = 1;

        const video = document.getElementById('cameraVideo');
        const canvas = document.getElementById('captureCanvas');
        const ctx = canvas.getContext('2d');
        const stage = document.getElementById('cameraStage');
        const faceBoxContainer = document.getElementById('faceBoxContainer');
        const toggle = document.getElementById('streamToggle');
        const liveBadge = document.getElementById('liveBadge');
        const liveSpinner = document.getElementById('liveSpinner');
        const errorDiv = document.getElementById('streamError');
        const branchSelect = document.getElementById('branchSelect');

        let enabled = false;
        let cameraStream = null;
        let cameraState = 'idle'; // idle, requesting, ready, denied, error
        let trackedFaces = [];
        let nextTrackId = 1;
        let analyzeTimer = null;
        let abortController = null;
        let active = false;

        const branchId = () => (branchSelect ? branchSelect.value : '{{ $branchId }}');

        function showPlaceholder(id) {
            ['placeholderOff','placeholderRequesting','placeholderDenied','placeholderError'].forEach(p => {
                document.getElementById(p).style.display = 'none';
            });
            if (id) document.getElementById(id).style.display = '';
        }

        async function startCamera() {
            cameraState = 'requesting';
            showPlaceholder('placeholderRequesting');
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }, audio: false });
                video.srcObject = cameraStream;
                await video.play();
                cameraState = 'ready';
                showPlaceholder(null);
            } catch (e) {
                if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
                    cameraState = 'denied';
                    showPlaceholder('placeholderDenied');
                } else {
                    cameraState = 'error';
                    showPlaceholder('placeholderError');
                }
            }
        }

        function stopCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(t => t.stop());
                cameraStream = null;
            }
            video.srcObject = null;
            cameraState = 'idle';
        }

        function captureFrame() {
            if (!video.videoWidth || !video.videoHeight) return null;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0);
            return new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.85));
        }

        function iou(a, b) {
            const x1 = Math.max(a.x, b.x), y1 = Math.max(a.y, b.y);
            const x2 = Math.min(a.x+a.width, b.x+b.width), y2 = Math.min(a.y+a.height, b.y+b.height);
            const inter = Math.max(0, x2-x1) * Math.max(0, y2-y1);
            const union = a.width*a.height + b.width*b.height - inter;
            return union > 0 ? inter/union : 0;
        }

        function centerDist(a, b, fw, fh) {
            return Math.hypot(a.x+a.width/2 - (b.x+b.width/2), a.y+a.height/2 - (b.y+b.height/2)) / Math.hypot(fw, fh);
        }

        function smoothBox(prev, cur) {
            const blend = (o, n) => o + (n - o) * BOX_SMOOTHING;
            return { x: blend(prev.x, cur.x), y: blend(prev.y, cur.y), width: blend(prev.width, cur.width), height: blend(prev.height, cur.height) };
        }

        function reconcile(frame) {
            const available = new Set(trackedFaces.map(f => f.trackId));
            const tracked = frame.faces.map(face => {
                let best = null, bestScore = -Infinity;
                for (const c of trackedFaces) {
                    if (!available.has(c.trackId)) continue;
                    if (face.member && c.member && face.member.member_code !== c.member.member_code) continue;
                    const overlap = iou(c.bounding_box, face.bounding_box);
                    const dist = centerDist(c.bounding_box, face.bounding_box, frame.image_width, frame.image_height);
                    if (overlap < 0.04 && dist > 0.14) continue;
                    const sameMember = (face.member?.member_code && face.member.member_code === c.member?.member_code) ? 0.5 : 0;
                    const score = overlap * 2 - dist + sameMember;
                    if (score > bestScore) { best = c; bestScore = score; }
                }
                if (!best) return { ...face, trackId: nextTrackId++, missedFrames: 0 };
                available.delete(best.trackId);
                return { ...face, bounding_box: smoothBox(best.bounding_box, face.bounding_box), trackId: best.trackId, missedFrames: 0 };
            });
            const missing = trackedFaces.filter(f => available.has(f.trackId) && f.missedFrames < MAX_MISSED_FRAMES).map(f => ({ ...f, missedFrames: f.missedFrames + 1 }));
            trackedFaces = [...tracked, ...missing];
        }

        function faceLabel(face) {
            if (face.registration === 'REGISTERED') return face.member?.full_name || 'Terdaftar';
            if (['FACE_TOO_SMALL','IMAGE_TOO_BLURRY','BAD_EXPOSURE','LOW_IMAGE_QUALITY'].includes(face.reason_code)) return 'Wajah kurang jelas';
            if (face.reason_code === 'AMBIGUOUS_FACE_MATCH') return 'Perlu verifikasi ulang';
            return 'Belum terdaftar';
        }

        function renderFaces(frame) {
            faceBoxContainer.innerHTML = '';
            if (!frame) return;
            trackedFaces.forEach(face => {
                const box = face.bounding_box;
                const left = (frame.image_width - box.x - box.width) / frame.image_width * 100;
                const el = document.createElement('div');
                el.className = 'stream-face-box ' + (face.registration === 'REGISTERED' ? 'registered' : 'unregistered') + (face.missedFrames ? ' stale' : '');
                el.style.cssText = `left:${left}%;top:${box.y/frame.image_height*100}%;width:${box.width/frame.image_width*100}%;height:${box.height/frame.image_height*100}%`;

                const label = document.createElement('span');
                label.className = 'face-label';
                const icon = face.registration === 'REGISTERED' ? 'check-circle' : 'scan-face';
                label.innerHTML = `<i class="ph ph-${icon}"></i> ${faceLabel(face)}`;
                if (face.member) label.innerHTML += ` <small>${face.member.member_code} · Terdaftar</small>`;
                el.appendChild(label);
                faceBoxContainer.appendChild(el);
            });

            const reg = trackedFaces.filter(f => f.registration === 'REGISTERED').length;
            document.getElementById('totalFaces').textContent = trackedFaces.length;
            document.getElementById('registeredFaces').textContent = reg;
            document.getElementById('unregisteredFaces').textContent = trackedFaces.length - reg;
        }

        let latestFrame = null;

        async function analyzeLoop() {
            if (!active) return;
            liveSpinner.style.display = '';
            try {
                const blob = await captureFrame();
                if (!blob) throw new Error('Kamera belum menghasilkan frame yang dapat dianalisis.');
                abortController = new AbortController();

                const formData = new FormData();
                formData.append('frame', blob, 'frame.jpg');
                formData.append('branch_id', branchId());

                const res = await fetch('{{ route("streaming.analyze") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: formData,
                    signal: abortController.signal,
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Error analisis');
                if (active) {
                    latestFrame = data;
                    reconcile(data);
                    renderFaces(data);
                    errorDiv.style.display = 'none';
                    stage.style.aspectRatio = data.image_width + ' / ' + data.image_height;
                }
            } catch (e) {
                if (active && e.name !== 'AbortError') {
                    errorDiv.textContent = e.message || 'Gambar belum dapat diproses.';
                    errorDiv.style.display = '';
                    trackedFaces = trackedFaces.filter(f => f.missedFrames < MAX_MISSED_FRAMES).map(f => ({ ...f, missedFrames: f.missedFrames + 1 }));
                    renderFaces(latestFrame);
                }
            } finally {
                if (active) {
                    liveSpinner.style.display = 'none';
                    analyzeTimer = setTimeout(() => analyzeLoop(), POLL_DELAY_MS);
                }
            }
        }

        toggle.addEventListener('change', function() {
            enabled = this.checked;
            if (enabled) {
                active = true;
                startCamera().then(() => {
                    if (cameraState === 'ready') {
                        liveBadge.style.display = '';
                        analyzeLoop();
                    }
                });
                document.getElementById('streamIcon').classList.add('active');
                document.getElementById('streamLabel').textContent = 'Streaming aktif';
            } else {
                active = false;
                if (abortController) abortController.abort();
                clearTimeout(analyzeTimer);
                stopCamera();
                showPlaceholder('placeholderOff');
                liveBadge.style.display = 'none';
                faceBoxContainer.innerHTML = '';
                trackedFaces = [];
                nextTrackId = 1;
                latestFrame = null;
                errorDiv.style.display = 'none';
                document.getElementById('totalFaces').textContent = '0';
                document.getElementById('registeredFaces').textContent = '0';
                document.getElementById('unregisteredFaces').textContent = '0';
                stage.style.aspectRatio = '16 / 9';
                document.getElementById('streamIcon').classList.remove('active');
                document.getElementById('streamLabel').textContent = 'Streaming nonaktif';
            }
        });

        window.retryCamera = function() {
            if (enabled) startCamera().then(() => {
                if (cameraState === 'ready') {
                    liveBadge.style.display = '';
                    analyzeLoop();
                }
            });
        };
    });
    </script>
    @endif
</x-app-layout>

