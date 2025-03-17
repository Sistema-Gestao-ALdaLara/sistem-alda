@extends('school.app')
@section('content')

<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Classes</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Página Inicial</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Classes</li>
                </ol>
            </nav>
        </div>

        <div class="col-md-6 col-sm-12 text-right">
            <a href="javascript:void(0);" class="btn btn-primary" data-toggle="modal"
                data-target="#exampleModalAdicionar">Adicionar Turma</a>
        </div>

    </div>
</div>

<div class="card-box mb-30">
    <br>
    <div class="pb-20">
        <div class="col-sm-12 col-md-4 mb-30">
            <div class="card card-box">
                <div class="card-body text-center">
                    <h5 class="card-title">Informatica</h5>
                    <h5 class="card-title">Tarde</h5>
                    <h5 class="card-title">Classe</h5>
                    <h5 class="card-title">Turma</h5>
                    <a href="#" class="btn btn-primary">Ver Boletin</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection