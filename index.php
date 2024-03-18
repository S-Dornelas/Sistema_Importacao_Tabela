<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Formulário de Importação</title>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col mt-5">

                <h1>Formulário de Importação</h1> <br>
               
                    <form action="importar_dados.php" method="post" enctype="multipart/form-data">
                        <label for="arquivo">Selecione o arquivo CSV:</label>
                        <input type="file" name="arquivo" id="arquivo" accept=".csv"><br><br>

                        <label for="cod_amb">Código do AMB:</label>
                        <input type="text" name="cod_amb" id="cod_amb"><br><br>

                        <label for="id_medico">Código Médico:</label>
                        <input type="text" id="id_medico" name="id_medico" required><br><br>

                        <input class="btn btn-success" type="submit" value="Importar">
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>