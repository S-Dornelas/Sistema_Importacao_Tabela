<?php 
    $banco = new mysqli ("localhost", "root", "", "yellow_v2_demosaude_teste");
    if ($banco->connect_errno) {
        echo "<p>Encontrei um erro $banco->errno --> $banco->connect_error</p>";
        die(); 
    }else{
        echo "<p>Banco de Dados conectado!</p>";
    }
    
    $banco->query("SET NAMES 'utf8'");
    $banco->query("SET character_set_connection=utf8");
    $banco->query("SET character_set_client=utf8");
    $banco->query("SET character_set_results=utf8");