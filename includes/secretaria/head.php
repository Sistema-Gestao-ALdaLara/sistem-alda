<title><?= $titulo ?? 'SECRETARIA | Alda Lara' ?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description" content="Sistema de Gestão Escolar - Escola Alda Lara">
<meta name="keywords" content="Escola, Alda Lara, Angola, Luanda, Secretaria, Matrículas">
<meta name="author" content="Escola Alda Lara">
<link rel="icon" href="../libraries/assets/images/favicon.ico" type="image/x-icon">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="../libraries/bower_components/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="../libraries/assets/icon/feather/css/feather.css">
<link rel="stylesheet" type="text/css" href="../libraries/assets/css/style.css">
<link rel="stylesheet" type="text/css" href="../libraries/assets/css/jquery.mCustomScrollbar.css">


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
    <!-- Favicon icon -->
    <link rel="icon" href="libraries\assets\images\favicon.ico" type="image/x-icon">
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="libraries\bower_components\bootstrap\css\bootstrap.min.css">
    <!-- feather Awesome -->
    <link rel="stylesheet" type="text/css" href="libraries\assets\icon\feather\css\feather.css">
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="libraries\assets\css\style.css">
    <link rel="stylesheet" type="text/css" href="libraries\assets\css\jquery.mCustomScrollbar.css">

    <style>
        .bg-img {
          width: 100%; /* Ou um valor específico, como 500px */
          height: auto; /* Defina a altura conforme necessário */
          background-image: url('../public/img/bg.jpg'); /* Caminho da imagem */
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