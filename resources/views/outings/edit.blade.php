@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-warning">
                <div class="card-header bg-warning text-dark fw-bold h4">
                    <i class="fas fa-edit me-2"></i> Edit Data Outing #{{ $outing->id }}
                </div>
                <form action="{{ route('outings.update', $outing->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal" class="form-label fw-bold">Tanggal</label>
                                <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal', $outing->tanggal->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nominal" class="form-label fw-bold">Nominal (IDR)</label>
                                <input type="number" step="0.01" name="nominal" id="nominal" class="form-control" value="{{ old('nominal', $outing->nominal) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="akun" class="form-label fw-bold">Akun</label>
                                <input type="text" name="akun" id="akun" class="form-control" value="{{ old('akun', $outing->akun) }}">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="voucher" class="form-label fw-bold">Voucher</label>
                                <input type="text" name="voucher" id="voucher" class="form-control" value="{{ old('voucher', $outing->voucher) }}">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama" class="form-label fw-bold">Nama</label>
                                <input type="text" name="nama" id="nama" class="form-control" value="{{ old('nama', $outing->nama) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nama_pt" class="form-label fw-bold">Nama PT</label>
                                <input type="text" name="nama_pt" id="nama_pt" class="form-control" value="{{ old('nama_pt', $outing->nama_pt) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="part" class="form-label fw-bold">Part</label>
                            <input type="text" name="part" id="part" class="form-control" value="{{ old('part', $outing->part) }}">
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label fw-bold">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" rows="3" class="form-control">{{ old('keterangan', $outing->keterangan) }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('outings.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-warning shadow-sm">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection