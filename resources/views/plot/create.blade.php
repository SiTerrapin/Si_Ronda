@extends('layouts.app')
@section('title', 'Tambah Jadwal Ronda - SiRonda')

@section('content')
<div class="container" style="max-width: 650px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">+ Tambah Jadwal Ronda</h3>
        <a href="{{ route('plot.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    <div class="modern-card p-4">
            <form method="POST" action="{{ route('plot.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Pilih Petugas Aktif</label>
                    <select name="petugas_id" class="form-select @error('petugas_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Petugas --</option>
                        @foreach($petugasList as $p)
                            <option value="{{ $p->id_petugas }}" {{ old('petugas_id') == $p->id_petugas ? 'selected' : '' }}>
                                {{ $p->nama_lengkap }} ({{ $p->alamat ?? 'Tanpa Blok' }})
                            </option>
                        @endforeach
                    </select>
                    @error('petugas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hari Ronda</label>
                        <select name="nama_hari" class="form-select @error('nama_hari') is-invalid @enderror" required>
                            @foreach($hariList as $hari)
                                <option value="{{ $hari }}" {{ old('nama_hari') === $hari ? 'selected' : '' }}>{{ $hari }}</option>
                            @endforeach
                        </select>
                        @error('nama_hari')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Spesifik <small class="text-secondary">(opsional)</small></label>
                        <input type="date" name="tanggal_ronda" class="form-control @error('tanggal_ronda') is-invalid @enderror" value="{{ old('tanggal_ronda') }}">
                        @error('tanggal_ronda')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pos Jaga</label>
                        <select name="pos_jaga" class="form-select @error('pos_jaga') is-invalid @enderror" required>
                            @foreach($posList as $pos)
                                <option value="{{ $pos }}" {{ old('pos_jaga') === $pos ? 'selected' : '' }}>{{ $pos }}</option>
                            @endforeach
                        </select>
                        @error('pos_jaga')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Shift</label>
                        <select name="shift" class="form-select @error('shift') is-invalid @enderror" required>
                            @foreach($shiftList as $shift)
                                <option value="{{ $shift }}" {{ old('shift') === $shift ? 'selected' : '' }}>{{ $shift }}</option>
                            @endforeach
                        </select>
                        @error('shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Status Komandan Regu</label>
                    <select name="is_leader" class="form-select @error('is_leader') is-invalid @enderror" required>
                        <option value="0" {{ old('is_leader', '0') === '0' ? 'selected' : '' }}>Anggota Biasa</option>
                        <option value="1" {{ old('is_leader') === '1' ? 'selected' : '' }}>Komandan Regu (Leader)</option>
                    </select>
                    @error('is_leader')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary w-100">Simpan Jadwal</button>
            </form>
    </div>
</div>
@endsection
