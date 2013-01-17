<?php
/**
 * cmd.php
 * for letssee plugin concept
 * http://ascii-table.com/ansi-escape-sequences.php for graph.mode help
 **/
if (!isset($_SESSION['mgrValidated'])) {
  return;
}

class LetsSee {
  
  private function showHelp($name) {
  	
    $class = get_class($this);
    $static = get_class_vars($class);
    $help_str_name = $name . "_documentation";
    
    if ($name != '' && isset($static[$help_str_name])) {
      return $static[$help_str_name];
  	} else {
      return 'Неизвестная команда';
    }
  }

  static $show_documentation = 'Просмотр переменных запроса и окружения

  [1;31mshow[0m [1mwhat[0m 
    [1mwhat[0m может быть 
    g|G|get|GET 
    p|P|post|POST
    r|R|request|REQUEST
    s|S|session|SESSION 
    a|A|server|SERVER';

  public function show($what='') {
    /*if (strcmp($user, 'demo') == 0 && strcmp($passwd, 'demo') == 0) {
      // If you need to handle more than one user you can create 
      // new token and save it in database
      // UPDATE users SET token = '$token' WHERE name = '$user'
      return md5($user . ":" . $passwd);
    } else {
      throw new Exception("Wrong Password");
    }*/
    global $modx;
    $did = $modx->documentIdentifier;

    if ($what == '') {
      //return $this->show_documentation;     
      return  $this->showHelp('show');
    }

    if (!isset($_SESSION['ls_plugin_data'][$did])) {
      throw new Exception("Не удалось передать данные в консоль");
    } else {
      $toShow = $_SESSION['ls_plugin_data'][$did];
      switch ($what) {
        case 'g':
        case 'get':
        case 'G':
        case 'GET':
          return $toShow['get'];
        break;
        case 'p':
        case 'post':
        case 'P':
        case 'POST':
          return $toShow['post'];
        break;
        case 'r':
        case 'request':
        case 'R':
        case 'REQUEST':
          return $toShow['request'];
        break;
        case 's':
        case 'session':
        case 'S':
        case 'SESSION':
          return $toShow['session'];
        break;
        case 'a':
        case 'server':
        case 'A':
        case 'SERVER':
          return $toShow['server'];
        break;
        default:
          return  $this->showHelp('show');
        break;
      }
    }    
  }

  static $doc_documentation = 'Просмотр полей документа

  [1;31mdoc[0m  [1mID[0m 
    [1mID[0m - id документа modx (текущий документ)';
  public function doc($id='') {
    global $modx;
    if ($id == '') {
      $id = $modx->documentIdentifier;
      //return  $this->showHelp('doc');
    }
    return $modx->getDocument($id);
  }
  

  static $whoami_documentation = "Просмотр ваших данных";
  public function whoami() {
    return array("ваш User Agent" => $_SERVER["HTTP_USER_AGENT"],
                 "ваш IP" => $_SERVER['REMOTE_ADDR'],
                 "вы перешли по ссылке с адреса" => $_SERVER["HTTP_REFERER"]);
  }

  
}

handle_json_rpc(new LetsSee());
?>