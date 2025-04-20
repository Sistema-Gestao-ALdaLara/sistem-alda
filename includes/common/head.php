    <head>
        <title><?php echo $title . ' | Alda Lara' ?></title>
        <!-- HTML5 Shim and Respond.js IE10 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 10]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
          <![endif]-->
        <!-- Meta -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="#">
        <meta name="keywords" content="Admin , Responsive, Landing, Bootstrap, App, Template, Mobile, iOS, Android, apple, creative app">
        <meta name="author" content="#">
        <?php require_once '../../includes/common/css_imports.php'; ?>
      
        <style>
            .bg-img {
                width: 100%;
                height: auto;
                background-image: url('../../public/img/bg.jpg');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }
            .table-custom {
                background: rgba(255, 255, 255, 0.2);
                backdrop-filter: blur(8px);
                border-radius: 10px;
                border: 1px solid rgba(255, 255, 255, 0.3);
                color: white;
            }
            .table-custom th, .table-custom td {
                padding: 12px;
                color: #ffffff;
            }
            .table-custom thead {
                background: rgba(7, 200, 206, 0.55);
                color: white;
                font-weight: bold;
            }
            .table-custom tbody tr:hover {
                background: rgba(255, 255, 255, 0.3);
                transition: 0.3s;
            }
            .card-table {
                background: rgba(19, 125, 171, 0.082);
                backdrop-filter: blur(10px);
                border-radius: 10px;
                border: 1px solid rgba(255, 255, 255, 0.3);
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                color: white !important;
            }
            .card-table .card-header {
                background: rgba(7, 200, 206, 0.836);
                color: white !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            }
            .card-table .table {
                background: transparent;
            }
            .action-buttons .btn {
                margin: 0 3px;
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
            }
            .filtros-container {
                background: rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 20px;
            }
            .filtros-container label {
                color: white;
                font-weight: bold;
            }
            .btn-filtrar {
                margin-top: 28px;
            }
            .btn-limpar {
                margin-top: 28px;
                background-color: #6c757d;
                border-color: #6c757d;
            }

            .badge-ativa { background-color: #28a745; }
            .badge-cancelada { background-color: #dc3545; }
            .badge-trancada { background-color: #ffc107; color: #000; }
            
            .numero-matricula {
                font-family: monospace;
                font-weight: bold;
            }
        </style>
        <style>
            .action-buttons .btn {
                margin: 0 3px;
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
            }
            .filtros-container {
                background: rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 20px;
            }
            .filtros-container label {
                color: white;
                font-weight: bold;
            }
            .btn-filtrar {
                margin-top: 28px;
            }
            .btn-limpar {
                margin-top: 28px;
                background-color: #6c757d;
                border-color: #6c757d;
            }
        </style>
    
    <style>
        /* ... (estilos iguais ao anterior) ... */
        .badge-diretor-geral {
            background-color: #4e73df;
        }
        .badge-diretor-pedagogico {
            background-color: #1cc88a;
        }
    </style>
    
    <style>
        .action-buttons .btn {
            margin: 0 3px;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .filtros-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .filtros-container label {
            color: white;
            font-weight: bold;
        }
        .btn-filtrar {
            margin-top: 28px;
        }
        .btn-limpar {
            margin-top: 28px;
            background-color: #6c757d;
            border-color: #6c757d;
        }
    </style>
     <style>
        /* ... (estilos anteriores) ... */
        .badge-secretaria {
            background-color: #6f42c1;
        }
        .badge-registrador {
            background-color: #fd7e14;
        }
    </style>
    
    <style>
        .badge-prova { background-color: #4680ff; }
        .badge-avaliacao_continua { background-color: #0e9e4a; }
        .badge-trabalho { background-color: #ffa21d; }
        .badge-recuperacao { background-color: #ff5252; }
        .badge-projeto { background-color: #9c27b0; }
        .card-estatistica { border-left: 4px solid; }
        .card-provas { border-left-color: #4680ff; }
        .card-avaliacoes { border-left-color: #0e9e4a; }
        .card-trabalhos { border-left-color: #ffa21d; }
    </style>

<style>

        .card-calendario {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background: rgba(7, 200, 206, 0.8);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }

        .horario-card {
            background: white;
            border-left: 4px solid #07c8ce;
            border-radius: 5px;
            margin-bottom: 10px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .horario-card h6 {
            color: #07c8ce;
            font-weight: bold;
        }

        .dia-semana {
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #07c8ce;
        }

        .fc-event {
            cursor: pointer;
        }

        .badge-curso {
            background-color: #6c757d;
        }

        .badge-turma {
            background-color: #17a2b8;
        }

        #calendar {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>


    <style>

        .card-disciplina {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            transition: transform 0.3s;
            margin-bottom: 20px;
        }

        .card-disciplina:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .card-disciplina .card-header {
            background: rgba(7, 200, 206, 0.55);
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            font-weight: bold;
        }

        .professor-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .disciplina-info {
            padding: 15px;
        }

        .disciplina-nome {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .professor-nome {
            font-size: 1rem;
            color: #07c8ce;
        }

        .empty-message {
            text-align: center;
            padding: 50px;
            color: #ccc;
            font-size: 1.2rem;
        }
    </style>

    <!-- CSS adicional para esta página -->
    <style>
        .profile-header {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card {
            border-left: 4px solid #4680ff;
            padding: 15px;
            margin-bottom: 15px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .badge-status {
            font-size: 14px;
            padding: 5px 10px;
        }
        @media (max-width: 768px) {
            .profile-img {
                width: 80px;
                height: 80px;
            }
        }
    </style>
    </head>