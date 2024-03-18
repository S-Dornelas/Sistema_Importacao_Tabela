<?php
include_once "conexao.php";

$linhas_importadas_amb = 0;
$linhas_importadas_medico = 0;
$linhas_nao_importadas_amb = 0;
$linhas_nao_importadas_medico = 0;

$cod_amb = $_POST['cod_amb'];
$id_medico = $_POST['id_medico'];
$arquivo = $_FILES['arquivo'];

if ($arquivo['type'] == "text/csv") {
    $dado_arquivo = fopen($arquivo['tmp_name'], "r");
    fgetcsv($dado_arquivo, 1000, ";");

    $codigo_amb_atual = intval($cod_amb);

    if ($dado_arquivo !== false) {
        $query_create_temp_table = "CREATE TEMPORARY TABLE temp_import_data ( 
                id INT AUTO_INCREMENT PRIMARY KEY,               
                cod_amb INT(100),
                id_medico INT(100),
                descricao VARCHAR(255),
                valor_procedimento FLOAT(10,2),
                valor_comissao FLOAT(10,2),
                ativo CHAR(1)
            )";
    }

    if ($banco->query($query_create_temp_table)) {

        while (($linha = fgetcsv($dado_arquivo, 1000, ";")) !== false) {
            foreach ($linha as &$valor) {
                $valor = mb_convert_encoding($valor, "UTF-8", "ISO-8859-1");
            }

            $ativo = "S";

            $comissao_formatada = str_replace(['R$', '.', ','], ['', '', '.'], $linha[3]);
            $comissao = floatval($comissao_formatada);

            $query_insert_temp_table = "INSERT INTO temp_import_data (
                                                cod_amb, 
                                                id_medico, 
                                                descricao, 
                                                valor_procedimento, 
                                                valor_comissao,
                                                ativo
                                                ) 
                                            VALUES 
                                                (?, ?, ?, ?, ?, ?)";
            $stmt_insert_temp_table = $banco->prepare($query_insert_temp_table);

            if ($stmt_insert_temp_table) {
                
                $codigo_amb_atual++;                   

                $stmt_insert_temp_table->bind_param('iisdds', $codigo_amb_atual, $id_medico, $linha[1], $linha[2], $comissao, $ativo);
                $stmt_insert_temp_table->execute();

                if ($stmt_insert_temp_table->affected_rows > 0) {
                    $linhas_importadas_amb++;
                } else {
                    $linhas_nao_importadas_amb++;
                    $linhas_nao_importadas_amb .= ", " . ($linha[0] ?? "NULL");
                }
            }
        }        

        $query_insert_amb = "INSERT IGNORE INTO amb (
                                    cod_amb,
                                    id_especialidade,    
                                    id_categoria,
                                    id_laudo,
                                    sigla,
                                    tipo_tabela,
                                    tabela_amb,    
                                    descricao,    
                                    ch,
                                    valor,    
                                    particular,
                                    prazo_volta,    
                                    id_orientacao_recomendada,
                                    codigo_integracao,
                                    prazo_previsto_resultado,
                                    material_coletar,
                                    recipiente,
                                    sem_comissao_medico,
                                    kit,
                                    laudo,    
                                    padrao_atendimentos,
                                    id_amb_pai,
                                    exame_laboratorial,
                                    dat_ult_integracao,    
                                    vlr_ult_integracao,    
                                    tiss_tipo,
                                    tiss_descricao,
                                    ativado,
                                    dat_criacao,
                                    user_criacao,
                                    dat_modificacao,    
                                    user_modificacao,    
                                    dat_remocao,
                                    user_remocao
                                    ) 
                                SELECT 
                                    temp_import_data.cod_amb,
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL,
                                    temp_import_data.descricao,
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL,
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL,
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL,
                                    NULL, 
                                    temp_import_data.ativo, 
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL,
                                    NULL,
                                    NULL 
                                FROM 
                                    temp_import_data";

        $stmt_insert_amb = $banco->prepare($query_insert_amb);

        if ($stmt_insert_amb->execute()) {
            $linhas_importadas_amb = $stmt_insert_amb->affected_rows;
        } else {
            echo "Erro ao inserir dados na tabela amb: " . $banco->error;
        }

        $query_insert_medico = "INSERT INTO medicos_amb (
                    cod_medico_amb, 
                    id_convenio_amb, 
                    id_amb, 
                    id_medico, 
                    valor_procedimento, 
                    valor_comissao, 
                    tipo_comissao, 
                    desconto, 
                    ativo, 
                    dat_created, 
                    user_created, 
                    dat_modified, 
                    user_modified, 
                    dat_removed, 
                    user_removed
                ) 
                SELECT 
                    NULL, 
                    NULL, 
                    temp_import_data.cod_amb, 
                    temp_import_data.id_medico, 
                    temp_import_data.valor_procedimento, 
                    temp_import_data.valor_comissao, 
                    NULL, 
                    NULL, 
                    temp_import_data.ativo, 
                    NULL, 
                    NULL, 
                    NULL, 
                    NULL, 
                    NULL, 
                    NULL
                FROM temp_import_data";

        $stmt_insert_medico = $banco->prepare($query_insert_medico);

        if ($stmt_insert_medico->execute()) {
            $linhas_importadas_medico = $stmt_insert_medico->affected_rows;
        } else {
            echo "Erro ao inserir dados na tabela medicos_amb: " . $banco->error;
        }

        $query_drop_temp_table = "DROP TEMPORARY TABLE IF EXISTS temp_import_data";
        $banco->query($query_drop_temp_table);
    } else {
        echo "Erro ao criar tabela temporária: " . $banco->error;
    }
} else {
    echo "Formato de arquivo inválido. É necessário enviar um arquivo CSV!";
}


echo "$linhas_importadas_amb linha(s) importada(s) para a tabela amb, $linhas_nao_importadas_amb linha(s) não importada(s) para a tabela amb.<br>";
echo "$linhas_importadas_medico linha(s) importada(s) para a tabela medicos_amb, $linhas_nao_importadas_medico linha(s) não importada(s) para a tabela medicos_amb.<br>";
