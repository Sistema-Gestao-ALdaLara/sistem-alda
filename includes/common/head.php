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
        <!--<style>
            .bg-img {
              width: 100%; /* Ou um valor específico, como 500px */
              height: auto; /* Defina a altura conforme necessário */
              background-image: url('../../public/img/bg.jpg'); /* Caminho da imagem */
              background-size: cover; /* Faz com que a imagem cubra toda a div */
              background-position: center; /* Centraliza a imagem */
              background-repeat: no-repeat; /* Evita repetições da imagem */
            }
            .table-custom {
                background: rgba(255, 255, 255, 0.2); /* Branco bem transparente */
                backdrop-filter: blur(8px); /* Efeito vidro fosco */
                border-radius: 10px; /* Bordas arredondadas */
                border: 1px solid rgba(255, 255, 255, 0.3); /* Borda branca fraca */
                color: white; /* Texto branco para contraste */
            }
            .table-custom th,
            .table-custom td {
                padding: 12px;
                color: #ffffff; /* Texto branco */
            }
            .table-custom thead {
                background: rgba(7, 200, 206, 0.55); /* Azul mais transparente */
                color: white;
                font-weight: bold;
            }
            .table-custom tbody tr:hover {
                background: rgba(255, 255, 255, 0.3); /* Efeito ao passar o mouse */
                transition: 0.3s;
            }
            /* Estilo específico para os cards que contêm tabelas */
            .card-table {
                background: rgba(19, 125, 171, 0.082); /* Fundo branco com transparência */
                backdrop-filter: blur(10px); /* Efeito vidro fosco */
                border-radius: 10px; /* Bordas arredondadas */
                border: 1px solid rgba(255, 255, 255, 0.3); /* Borda sutil */
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra leve */
                color: white !important;
            }
            /* Ajuste no cabeçalho do card */
            .card-table .card-header {
                background: rgba(7, 200, 206, 0.836); /* Azul translúcido */
                color: white !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            }
            /* Estilo da tabela dentro do card */
            .card-table .table {
                background: transparent; /* Mantém a tabela transparente dentro do card */
            }
        </style>
        -->
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
    </head>