//<?php
/**
 * letsSee plugin 
 * concept
**/
if (!isset($_SESSION['mgrValidated'])) {
  return;
}

//параметры пока все тут
$needJq = true;

$e = &$modx->event;

switch ($e->name) {
  case 'OnWebPageInit':
    //ответ на аяксы
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && 
        isset($_GET['letssee']) && $_GET['letssee'] == '1') {
      require_once(MODX_BASE_PATH.'assets/plugins/letssee/json-rpc.php');
      require_once(MODX_BASE_PATH.'assets/plugins/letssee/cmd.php');
      die();
    }

    $did = $modx->documentIdentifier;

    //готовим данные для ответа команде show
    if (isset($_SESSION['ls_plugin_data'][$did])) {
      unset($_SESSION['ls_plugin_data'][$did]);
    }

    $toSave['server'] = var_export($_SERVER, true);
    //$toSave['request'] = var_export($_REQUEST, true);
    $toSave['request'] = $_REQUEST;
    $toSave['post'] = var_export($_POST, true);
    $toSave['get'] = var_export($_GET, true);

    //сохраняем сессию
    $tmpSesAr = array();

    if (is_array($_SESSION)) {
      foreach ($_SESSION as $key => $value) {
        if ($key != 'ls_plugin_data') {
          $tmpSesAr[$key] = $value;
        }
      }
    }
    $toSave['session'] = var_export($tmpSesAr, true);
    
    $_SESSION['ls_plugin_data'][$did] = $toSave;
  break;
  case 'OnWebPagePrerender':
    $o = &$modx->documentOutput;
    //проверяем, есть ли уже вызов jquery    
    /*if (strpos($o, 'jquery')) {
      $needJq = false;
    }*/
    $o = str_replace('</head>', '<link rel="stylesheet" href="assets/plugins/letssee/jquery.terminal.css"/></head>', $o);
    $o = str_replace('</head>', '<script type="text/javascript" src="assets/plugins/letssee/jquery.terminal-min.js"></script></head>', $o);
    $o = str_replace('</head>', '<script type="text/javascript" src="assets/plugins/letssee/jquery.mousewheel-min.js"></script></head>', $o);
    
    $scr = <<<'SCR'

<script>


String.prototype.strip = function(char) {
    return this.replace(new RegExp("^" + char + "*"), '').
        replace(new RegExp(char + "*$"), '');
}


$.extend_if_has = function(desc, source, array) {
    for (var i=array.length;i--;) {
        if (typeof source[array[i]] != 'undefined') {
            desc[array[i]] = source[array[i]];
        }
    }
    return desc;
};


(function($) {
    $.fn.tilda = function(eval, options) {
        if ($('body').data('tilda')) {
            return $('body').data('tilda').terminal;
        }
        this.addClass('tilda');
        options = options || {};
        eval = eval || function(command, term) {
            term.echo("you don't set eval for tilda");
        };        

        var settings = {
            prompt: '>',
            name: 'tilda',
            height: 200,
            enabled: false,
            tabcompletion: true,
            greetings: "Type 'help' to invoke help. ~ to close. 'clean' to clear console",
            keydown: function(e, t) {
              if (e.which == 9) {
                var command = t.get_command();
                //alert (command);
                
                if (!command.match(' ')) { // complete only first word
                  t.pause();
                  //$.jrpc is helper function which
                  //creates json-rpc request
                  $.jrpc("[+page+]", 
                    1, 
                    "tab",
                    [command], 
                    function(data) {
                      t.resume();
                      if (data.error) {
                        t.error(data.error.message);
                      } else {
                        if (typeof data.result == 'boolean') {
                          t.echo(data.result ?
                                    'success' :
                                    'fail');
                        } else {
                          t.set_command(data.result);
                          /*var len = data.result.length;
                          for(var i=0;i<len; ++i) {
                            t.echo(data.result[i].join(' | '));
                          }*/
                        }
                      }
                    },
                    function(xhr, status, error) {
                      t.error('[AJAX] ' + status + 
                         ' - Server reponse is: \n' + 
                         xhr.responseText);
                         t.resume();
                    }); // rpc call
                }
                return false;
              }              
            }
        };

        if (options) {
            $.extend(settings, options);
        }
        this.append('<div class="td"></div>');
        var self = this;
        self.terminal = this.find('.td').terminal("[+page+]", settings);

        var focus = false;
        $(document.documentElement).keypress(function(e) {
            if (e.which == 96) {
                self.slideToggle('fast');
                self.terminal.set_command('');
                self.terminal.focus(focus = !focus);
                self.terminal.attr({
                    scrollTop: self.terminal.attr("scrollHeight")
                });
            }
        });
        $('body').data('tilda', this);
        this.hide();
        return self;
    };
})(jQuery);

//--------------------------------------------------------------------------
jQuery(document).ready(function($) {
    var id = 1;

    $('#tilda').tilda(function(command, terminal) {});
});
</script>
SCR;
    $div = '<div id="tilda"></div>';
    $o = str_replace('</body>', $scr.'</body>', $o);
    $o = str_replace('</body>', $div.'</body>', $o);
    
    //заменяем плейсхолдеры
    $page = $modx->makeUrl($modx->documentIdentifier,'','letssee=1','full');
    $o = str_replace('[+page+]', $page, $o);
  break;
}
//?>