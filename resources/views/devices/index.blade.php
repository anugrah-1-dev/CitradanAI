<x-app-layout>
    <x-slot name="header">Perangkat</x-slot>

    <style>
        .device-eyebrow{font-size:.65rem;font-weight:700;letter-spacing:.12em;color:#a3e635;margin-bottom:.25rem}
        .device-title-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:.25rem;flex-wrap:wrap;gap:.5rem}
        .device-title{font-size:1.35rem;font-weight:700;color:#fff}
        .device-desc{font-size:.78rem;color:#9ca3af;margin-bottom:1.2rem}
        .device-actions{display:flex;gap:.5rem}
        .btn-secondary{display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .8rem;border-radius:.35rem;background:#262926;border:1px solid #3f3f46;color:#d1d5db;font-size:.72rem;font-weight:600;cursor:pointer;transition:background .15s}
        .btn-secondary:hover{background:#333633}
        .btn-primary{display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .8rem;border-radius:.35rem;background:#65a30d;border:1px solid #65a30d;color:#fff;font-size:.72rem;font-weight:600;cursor:pointer;transition:background .15s}
        .btn-primary:hover{background:#4d7c0f}

        .device-overview{display:grid;grid-template-columns:repeat(4,1fr);margin-bottom:1rem;border-radius:.5rem;background:#1a1d1a;border:1px solid #2d302d}
        @media(max-width:768px){.device-overview{grid-template-columns:repeat(2,1fr)}}
        .device-overview>div{display:flex;align-items:center;gap:.65rem;padding:.85rem 1.1rem;border-right:1px solid #2d302d}
        .device-overview>div:last-child{border:0}
        .device-overview i{font-size:1.25rem;color:#65a30d}
        .device-overview span{display:flex;flex-direction:column}
        .device-overview small{color:#6b7280;font-size:.55rem}
        .device-overview strong{font-size:1.05rem;color:#fff}

        .device-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.9rem}
        @media(max-width:1024px){.device-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:640px){.device-grid{grid-template-columns:1fr}}

        .device-card{padding:1.1rem;border-radius:.5rem;background:#1a1d1a;border:1px solid #2d302d}
        .device-card>header{display:grid;grid-template-columns:2.8rem 1fr;align-items:center;gap:.65rem}
        .device-icon{width:2.6rem;height:2.6rem;display:grid;place-items:center;border-radius:.5rem;color:#65a30d;background:#1a2e05;font-size:1.3rem}
        .device-card.device-offline .device-icon{color:#f87171;background:#2a1010}

        .connection-label{display:flex;align-items:center;gap:.3rem;font-size:.5rem;font-weight:700;letter-spacing:.08em}
        .connection-label .indicator{width:.4rem;height:.4rem;border-radius:50%;background:currentColor}
        .connection-label.online{color:#22c55e}
        .connection-label.offline{color:#ef4444}

        .device-card h2{margin:.15rem 0 0;font-size:.82rem;color:#e5e7eb}
        .device-card header>div:nth-child(2)>span:last-child{color:#6b7280;font-size:.55rem}

        .device-location{display:flex;align-items:center;gap:.4rem;margin:.95rem 0;padding:.5rem;border-radius:.25rem;color:#9ca3af;background:#111311;font-size:.62rem}
        .device-location i{font-size:.85rem}

        .device-stats{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
        .device-stats>div{display:flex;flex-direction:column;gap:.15rem}
        .device-stats span{color:#6b7280;font-size:.55rem}
        .device-stats strong{font-size:.62rem;color:#d1d5db}

        .device-card footer{display:flex;align-items:center;gap:.5rem;margin-top:1rem;padding-top:.85rem;border-top:1px solid #2d302d}
        .device-card footer small{color:#6b7280;font-size:.55rem}
        .text-button{display:inline-flex;align-items:center;gap:.25rem;margin-left:auto;padding:.25rem .45rem;background:none;border:none;color:#a3e635;font-size:.55rem;font-weight:600;cursor:pointer;border-radius:.25rem}
        .text-button:hover{background:#1a2e05}
        .text-button i{font-size:.85rem}

        .text-success{color:#22c55e}
        .text-danger{color:#ef4444}

        .security-callout{display:flex;gap:.65rem;margin-top:1.2rem;padding:.85rem 1.1rem;border-radius:.5rem;border:1px solid #365314;background:#1a2e05;color:#65a30d}
        .security-callout i{font-size:1.3rem;flex:0 0 auto}
        .security-callout strong{font-size:.72rem;display:block;color:#a3e635}
        .security-callout p{margin:.25rem 0 0;color:#6b7280;font-size:.62rem;line-height:1.5}

        /* Modal */
        .modal-backdrop{position:fixed;inset:0;z-index:50;background:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center;padding:1rem}
        .modal-content{width:100%;max-width:28rem;background:#1a1d1a;border:1px solid #2d302d;border-radius:.65rem;padding:1.5rem;box-shadow:0 25px 50px rgba(0,0,0,.5)}
        .modal-title{font-size:1rem;font-weight:700;color:#fff;margin-bottom:.15rem}
        .modal-desc{font-size:.68rem;color:#6b7280;margin-bottom:1rem}
        .modal-form label{display:flex;flex-direction:column;gap:.25rem;margin-bottom:.75rem;font-size:.68rem;font-weight:600;color:#d1d5db}
        .modal-form input[type=text]{width:100%;padding:.45rem .6rem;background:#111311;border:1px solid #3f3f46;border-radius:.35rem;color:#e5e7eb;font-size:.72rem}
        .modal-form input[type=text]:focus{outline:none;border-color:#65a30d}
        .capability-checks{display:flex;gap:1rem;margin-bottom:.75rem}
        .capability-checks label{display:flex;align-items:center;gap:.35rem;font-size:.68rem;color:#d1d5db;cursor:pointer}
        .form-alert{padding:.5rem .65rem;border-radius:.3rem;background:#451a1a;color:#fca5a5;font-size:.68rem;margin-bottom:.75rem;border:1px solid #7f1d1d}
        .modal-actions{display:flex;justify-content:flex-end;gap:.5rem;margin-top:1rem}

        .credential-display{display:flex;align-items:center;gap:.5rem;padding:.65rem;background:#111311;border:1px solid #3f3f46;border-radius:.35rem;margin-bottom:1rem}
        .credential-display code{flex:1;font-size:.72rem;color:#a3e635;word-break:break-all;font-family:'Courier New',monospace}

        .empty-state{text-align:center;padding:3rem 1rem;color:#6b7280}
        .empty-state i{font-size:3rem;color:#3f3f46;margin-bottom:.5rem;display:block}
        .empty-state strong{color:#d1d5db;font-size:.9rem;display:block;margin-bottom:.25rem}
    </style>

    <div>
        <p class="device-eyebrow">INFRASTRUKTUR AKSES</p>
        <div class="device-title-row">
            <h1 class="device-title">Perangkat</h1>
            @if($apiReady)
            <div class="device-actions">
                <button class="btn-secondary" onclick="refreshDevices()"><i class="ph ph-arrow-clockwise"></i> Segarkan</button>
                <button class="btn-primary" onclick="openRegisterModal()"><i class="ph ph-plus"></i> Daftarkan kiosk</button>
            </div>
            @endif
        </div>
        <p class="device-desc">Pantau kesehatan kamera, heartbeat, dan kredensial kiosk.</p>
    </div>

    @if(! $apiReady)
        <div style="padding:2rem;text-align:center;color:#f87171;background:#1a1d1a;border:1px solid #7f1d1d;border-radius:.5rem">
            <i class="ph ph-warning-circle" style="font-size:2.5rem;display:block;margin-bottom:.5rem"></i>
            <strong style="font-size:.9rem">API Biometrik Tidak Tersedia</strong>
            <p style="color:#9ca3af;font-size:.72rem;margin-top:.35rem">Pastikan service biometrik (bisagym-api) berjalan di port 8001 dan konfigurasi .env sudah benar.</p>
        </div>
    @else
        <div class="device-overview" id="deviceOverview">
            <div><i class="ph ph-router"></i><span><small>Total perangkat</small><strong id="statTotal">{{ count($devices) }}</strong></span></div>
            <div><i class="ph ph-wifi-high"></i><span><small>Online</small><strong id="statOnline">{{ count(array_filter($devices, fn($d) => $d['online'])) }}</strong></span></div>
            <div><i class="ph ph-camera"></i><span><small>Kamera siap</small><strong id="statCamera">{{ count(array_filter($devices, fn($d) => ($d['camera_status'] ?? '') === 'Siap')) }}</strong></span></div>
            <div><i class="ph ph-wifi-slash"></i><span><small>Perlu perhatian</small><strong id="statOffline">{{ count(array_filter($devices, fn($d) => !$d['online'])) }}</strong></span></div>
        </div>

        <div class="device-grid" id="deviceGrid">
            @if(empty($devices))
                <div class="empty-state" style="grid-column:1/-1">
                    <i class="ph ph-cpu"></i>
                    <strong>Belum ada perangkat terdaftar</strong>
                    <p style="font-size:.72rem">Klik "Daftarkan kiosk" untuk menambah perangkat pertama.</p>
                </div>
            @else
                @foreach($devices as $device)
                    <article class="device-card {{ !$device['online'] ? 'device-offline' : '' }}" data-id="{{ $device['id'] }}">
                        <header>
                            <div class="device-icon"><i class="ph ph-cpu"></i></div>
                            <div>
                                <div class="connection-label {{ $device['online'] ? 'online' : 'offline' }}"><span class="indicator"></span>{{ $device['online'] ? 'ONLINE' : 'OFFLINE' }}</div>
                                <h2>{{ $device['name'] }}</h2>
                                <span>{{ $device['code'] }}</span>
                            </div>
                        </header>
                        <div class="device-location"><i class="ph ph-map-pin"></i> {{ $device['location'] }}</div>
                        <div class="device-stats">
                            <div><span>Heartbeat terakhir</span><strong>{{ $device['last_seen_at'] ? \Carbon\Carbon::parse($device['last_seen_at'])->diffForHumans() : 'Belum pernah' }}</strong></div>
                            <div><span>Status kamera</span><strong class="{{ $device['camera_status'] === 'Siap' ? 'text-success' : 'text-danger' }}">{{ $device['camera_status'] }}</strong></div>
                            <div><span>Versi software</span><strong>{{ $device['version'] === '-' ? '-' : 'v'.$device['version'] }}</strong></div>
                            <div><span>Kemampuan</span><strong>{{ !empty($device['capabilities']) ? implode(' + ', $device['capabilities']) : '-' }}</strong></div>
                        </div>
                        <footer>
                            <label class="switch">
                                <input type="checkbox" {{ $device['active'] ? 'checked' : '' }} onchange="toggleDevice('{{ $device['id'] }}', this.checked)">
                                <span></span>
                            </label>
                            <small>{{ $device['active'] ? 'Perangkat aktif' : 'Perangkat nonaktif' }}</small>
                            <button class="text-button" onclick="rotateCredential('{{ $device['id'] }}', '{{ addslashes($device['name']) }}')"><i class="ph ph-key"></i> Rotasi kredensial</button>
                        </footer>
                    </article>
                @endforeach
            @endif
        </div>

        <div class="security-callout">
            <i class="ph ph-check-circle"></i>
            <div>
                <strong>Autentikasi perangkat aktif</strong>
                <p>Kredensial baru hanya ditampilkan satu kali. Simpan langsung pada konfigurasi kiosk yang bersangkutan.</p>
            </div>
        </div>

        <!-- Register Modal -->
        <div class="modal-backdrop" id="registerModal" style="display:none">
            <div class="modal-content">
                <h3 class="modal-title">Daftarkan kiosk baru</h3>
                <p class="modal-desc">Kredensial hanya ditampilkan sekali setelah perangkat dibuat.</p>
                <form class="modal-form" id="registerForm" onsubmit="submitRegister(event)">
                    <div class="form-alert" id="registerError" style="display:none"></div>
                    <label>Nama perangkat<input type="text" name="name" required minlength="2" placeholder="Contoh: Kiosk Studio 2"></label>
                    <label>Lokasi<input type="text" name="location" required placeholder="Contoh: Lantai 2"></label>
                    <label>Kode perangkat<input type="text" name="device_code" required minlength="3" placeholder="KIOSK-004"></label>
                    <div class="capability-checks">
                        <label><input type="checkbox" name="rgb" checked> Kamera RGB</label>
                        <label><input type="checkbox" name="active_liveness" checked> Active liveness</label>
                        <label><input type="checkbox" name="depth"> Kamera depth</label>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-secondary" onclick="closeRegisterModal()">Batal</button>
                        <button type="submit" class="btn-primary" id="registerBtn"><i class="ph ph-plus"></i> Daftarkan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Credential Modal -->
        <div class="modal-backdrop" id="credentialModal" style="display:none">
            <div class="modal-content">
                <h3 class="modal-title">Kredensial satu kali</h3>
                <p class="modal-desc" id="credentialDesc"></p>
                <div class="credential-display">
                    <code id="credentialValue"></code>
                    <button class="btn-secondary" onclick="copyCredential()"><i class="ph ph-copy"></i> <span id="copyLabel">Salin</span></button>
                </div>
                <div class="modal-actions">
                    <button class="btn-primary" onclick="closeCredentialModal()">Saya sudah menyimpan</button>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <div id="toast" style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;z-index:60;padding:.6rem 1rem;border-radius:.4rem;font-size:.72rem;font-weight:600;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,.4)"></div>
    @endif

    @if($apiReady)
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrf = document.querySelector('meta[name=csrf-token]').content;

        function showToast(msg, isError) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.style.background = isError ? '#7f1d1d' : '#14532d';
            t.style.display = '';
            setTimeout(() => t.style.display = 'none', 3500);
        }

        window.refreshDevices = function() {
            fetch('{{ route("devices.refresh") }}', { headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => { if (data.devices) location.reload(); })
                .catch(() => showToast('Gagal menyegarkan data perangkat.', true));
        };

        window.openRegisterModal = () => document.getElementById('registerModal').style.display = '';
        window.closeRegisterModal = () => { document.getElementById('registerModal').style.display = 'none'; document.getElementById('registerForm').reset(); document.getElementById('registerError').style.display = 'none'; };

        window.submitRegister = function(e) {
            e.preventDefault();
            const form = document.getElementById('registerForm');
            const fd = new FormData(form);
            const btn = document.getElementById('registerBtn');
            btn.disabled = true;
            btn.innerHTML = 'Mendaftarkan...';

            fetch('{{ route("devices.store") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: fd.get('name'),
                    device_code: fd.get('device_code'),
                    location: fd.get('location'),
                    rgb: form.querySelector('[name=rgb]').checked,
                    active_liveness: form.querySelector('[name=active_liveness]').checked,
                    depth: form.querySelector('[name=depth]').checked,
                }),
            })
            .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
            .then(({ ok, data }) => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-plus"></i> Daftarkan';
                if (!ok) {
                    const errDiv = document.getElementById('registerError');
                    errDiv.textContent = data.message || 'Gagal mendaftarkan perangkat.';
                    errDiv.style.display = '';
                    return;
                }
                closeRegisterModal();
                if (data.credential) {
                    showCredential(data.device?.name || 'Perangkat', data.credential);
                }
                showToast('Perangkat berhasil didaftarkan.', false);
                setTimeout(() => location.reload(), 1500);
            })
            .catch(() => { btn.disabled = false; btn.innerHTML = '<i class="ph ph-plus"></i> Daftarkan'; showToast('Gagal mendaftarkan perangkat.', true); });
        };

        function showCredential(name, cred) {
            document.getElementById('credentialDesc').textContent = name + ' · kredensial ini tidak dapat dilihat lagi setelah dialog ditutup.';
            document.getElementById('credentialValue').textContent = cred;
            document.getElementById('copyLabel').textContent = 'Salin';
            document.getElementById('credentialModal').style.display = '';
        }
        window.closeCredentialModal = () => document.getElementById('credentialModal').style.display = 'none';
        window.copyCredential = function() {
            navigator.clipboard?.writeText(document.getElementById('credentialValue').textContent);
            document.getElementById('copyLabel').textContent = 'Tersalin';
        };

        window.toggleDevice = function(id, active) {
            fetch('{{ url("/devices") }}/' + id + '/toggle', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ is_active: active }),
            })
            .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
            .then(({ ok, data }) => {
                if (!ok) { showToast(data.message || 'Gagal.', true); return; }
                showToast('Perangkat berhasil ' + (active ? 'diaktifkan' : 'dinonaktifkan') + '.', false);
            })
            .catch(() => showToast('Gagal mengubah status perangkat.', true));
        };

        window.rotateCredential = function(id, name) {
            if (!confirm('Rotasi kredensial untuk ' + name + '? Kredensial lama tidak akan berfungsi lagi.')) return;
            fetch('{{ url("/devices") }}/' + id + '/rotate-credential', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            })
            .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
            .then(({ ok, data }) => {
                if (!ok) { showToast(data.message || 'Gagal.', true); return; }
                showCredential(name, data.credential);
            })
            .catch(() => showToast('Gagal merotasi kredensial.', true));
        };
    });
    </script>
    @endif
</x-app-layout>

