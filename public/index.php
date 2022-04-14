<?php

//echo phpinfo();
//exit;

  /******************  
    SCRIPT PARA BUSCAR REPOSITORIOS DESTAQUES NO GITHUB DE ACORDO COM A LINGUAGEM DE PROGRAMACAO ESCOLHIDA
  *******************/

  $username = 'root';
  $password = '';
  $options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    #PDO::MYSQL_ATTR_SSL_CA => '/path/to/cacert.pem',
    #PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
  );

  try {

    $conn = new PDO('mysql:host=mysql;dbname=dbdesafio;port=3306', $username, $password,$options);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  } catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
  }
  
  function convert($text) {
    $text = trim($text);
    return '<p>' . preg_replace('/[\r\n]+/', '</p><p>', $text) . '</p>';
  }

?>

<!doctype html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Desafio Dev</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <style type="text/css">
      .container {
        margin-top: 5em;
      }

    </style>
  </head>

  <body class="bg-light">

    <nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-dark">
      <a class="navbar-brand mr-auto mr-lg-0" href="#">Desafio Dev</a>
    </nav>

    <main role="main" class="container">

      <div class="my-3 p-3 bg-white rounded shadow-sm">

        <?php 

          if(isset($_POST['search_type'])){ $post_term = $_POST['search_type']; } else { $post_term = ''; }

          $url = 'https://api.github.com/search/repositories?q='.$post_term.'+language:'.$post_term.'&sort=stars&per_page=5';

          $cInit = curl_init();
          curl_setopt($cInit, CURLOPT_URL, $url);
          curl_setopt($cInit, CURLOPT_RETURNTRANSFER, 1); // 1 = TRUE
          curl_setopt($cInit, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
          curl_setopt($cInit, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
          curl_setopt($cInit, CURLOPT_USERPWD, '$user:$pass' );

          $output = curl_exec($cInit);

          $info = curl_getinfo($cInit, CURLINFO_HTTP_CODE);

          //$result = json_decode($output, true);
          $result = json_decode($output);

          curl_close($cInit);

          //LIMPA OS REGISTROS NO BANCO DE DADOS
          if(isset($_POST['limpar_term'])) {
            $stmt = $conn->prepare('TRUNCATE repositorio');
            $stmt->execute();
          } 

          //DELETA O REGISTRO NO BANCO DE DADOS USANDO ID
          if(isset($_POST['delete_term'])) {
            $stmt = $conn->prepare('DELETE FROM repositorio WHERE id = "'.$_POST['delete_term'].'"');
            $stmt->execute();
          } 

          //INSERE O RETORNO DA BUSCA NA API NO BANCO DE DADOS
          if(isset($post_term)) {
            //$result_items = $result->items;
            if (is_array($result->items) || is_object($result->items)) //corrige waring
            {
                foreach ($result->items as $key => $value) { 
                  $stmt = $conn->prepare('INSERT INTO repositorio (nome, linguagem, link) VALUES ("'.$value->full_name.'", "'.$value->language.'", "'.$value->owner->html_url.'")');
                  $stmt->execute();
                }
            }
          }

          // if (isset($_POST['search_type'])) {
          //   echo $_POST['search_type'];
          // }
          //exit;


        ?>

        <div class="dropdown">

          <p>
            Clique no botão abaixo para escolher uma linguagem de programação e visualizar os cinco repositórios destaques do GitHub.
          </p>
<!--
          <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Escolha a linguagem
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" href="?term=php">PHP</a>
            <a class="dropdown-item" href="?term=python">Python</a>
            <a class="dropdown-item" href="?term=java">Java</a>
            <a class="dropdown-item" href="?term=javascript">Javascript</a>
            <a class="dropdown-item" href="?term=shell">Shell</a>
          </div>
-->

        <form name="searchForm" id="searchForm" method="POST" />
          <div class="input-append">
            <input class="span2" id="search_type" name="search_type" type="hidden">
            <div class="btn-group">
              <button class="btn btn-secondary btn-sm dropdown-toggle" id="dropdownMenuButton" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Escolha a linguagem
                <span class="caret"></span>
              </button>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item" onclick="$('#search_type').val('php'); $('#searchForm').submit()">PHP</a>
                <a class="dropdown-item" onclick="$('#search_type').val('python'); $('#searchForm').submit()">Python</a>
                <a class="dropdown-item" onclick="$('#search_type').val('java'); $('#searchForm').submit()">Java</a>
                <a class="dropdown-item" onclick="$('#search_type').val('javascript'); $('#searchForm').submit()">Javascript</a>
                <a class="dropdown-item" onclick="$('#search_type').val('shell'); $('#searchForm').submit()">Shell</a>
              </div>
            </div>
          </div>
        </form>

        </div>

      </div>

      <div class="my-3 p-3 bg-white rounded shadow-sm">
        <h6 class="border-bottom border-gray pb-2 mb-0">Repositórios Visualizados</h6>

          <?php

            $stmt = $conn->prepare('SELECT id, nome, linguagem, link FROM repositorio ORDER BY id DESC');
            $stmt->execute();

            $result3 = $stmt->fetchAll();  

            if ( count($result3) ) {

              foreach($result3 as $row) { ?>

                <div class="media text-muted pt-3">
                  <div class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
                      
                      <strong><?php echo $row['id'] ?># <span class="badge bg-info text-dark"><?php echo $row['linguagem']; ?></span> Nome do repositório: <?php echo $row['nome']; ?></strong><br />
                      <strong>Link: <a target="_blank" href="<?php echo $row['link']; ?>"><?php echo $row['link']; ?></a></strong><br />
                      <br />
                      <div class="d-flex justify-content-between w-100">

                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target=".bd-example-modal-lg-<?php echo $row['id']; ?>">Readme</button>
                        
                        <form name="delete" id="delete" method="POST" />
                          <input class="span2" id="delete_term" name="delete_term" type="hidden">
                          <button onclick="$('#delete_term').val('<?php echo $row['id']; ?>'); $('#delete').submit()" type="button" class="btn btn-danger btn-sm">Delete</button>
                        </form>

                        
                      </div>
                    
                      <div class="modal fade bd-example-modal-lg-<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">

                            <div class="modal-header">
                              <h5 class="modal-title h4" id="myLargeModalLabel">Readme</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                <span aria-hidden="true">×</span>
                              </button>
                            </div>
                            <div class="modal-body">

                            <?php
                               
                              $urlDet = 'https://api.github.com/repos/'.$row['nome'].'/readme';

                              $cInitDet = curl_init();
                              curl_setopt($cInitDet, CURLOPT_URL, $urlDet);
                              curl_setopt($cInitDet, CURLOPT_RETURNTRANSFER, 1); // 1 = TRUE
                              curl_setopt($cInitDet, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                              curl_setopt($cInitDet, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                              curl_setopt($cInit, CURLOPT_USERPWD, '$user:$pass' );

                              $outputDet = curl_exec($cInitDet);

                              $info = curl_getinfo($cInitDet, CURLINFO_HTTP_CODE);

                              //$result = json_decode($output, true);
                              $detalhes = json_decode($outputDet);

                              curl_close($cInitDet);

                              echo convert(base64_decode($detalhes->content));
                                
                            ?>

                            <?php //echo '<pre>'.print_r($detalhes, true).'</pre>'; ?>

                            </div>

                          </div> <!-- modal-content -->
                        </div>
                      </div> <!-- modal -->


                  </div> <!-- media-body -->
                </div> <!-- media -->
                


            <?php 

              } //end foreach
            
            } else {

              echo "Nennhum resultado retornado.";

            }

          ?>

      </div>

            <small class="d-block text-right mt-3">

              <form name="limpar" id="limpar" method="POST" />
                <input class="span2" id="limpar_term" name="limpar_term" type="hidden">
                <button onclick="$('#limpar_term').val('ok'); $('#limpar').submit()" type="button" class="btn btn-dark btn-sm">Limpar todos registros</button>
              </form>

          </small>



    </main>

    <!-- Principal JavaScript do Bootstrap
    ================================================== -->
    <!-- JavaScript (Opcional) -->
    <!-- jQuery primeiro, depois Popper.js, depois Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  </body>
</html>
