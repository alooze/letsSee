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
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      require_once(MODX_BASE_PATH.'assets/plugins/letssee/json-rpc.php');
      require_once(MODX_BASE_PATH.'assets/plugins/letssee/cmd.php');
      die();
    }

    //готовим данные для ответа команде show
    if (isset($_SESSION['ls_plugin_data'])) {
      unset($_SESSION['ls_plugin_data']);
    }

    $toSave['server'] = var_export($_SERVER, true);
    $toSave['request'] = var_export($_REQUEST, true);
    $toSave['session'] = var_export($_SESSION, true);
    $toSave['post'] = var_export($_POST, true);
    $toSave['get'] = var_export($_GET, true);
    $_SESSION['ls_plugin_data'] = $toSave;
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
            greetings: "Type 'help' to invoke help. ~ to close"
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

    $('#tilda').tilda(function(command, terminal) {
      //terminal.echo('you type command "' + command + '"');
      
      /*terminal.pause();
      $.jrpc("[+page+]", 
           id++, 
           "query", 
           [command], 
           function(data) {
               terminal.resume();
               if (data.error) {
                   terminal.error(data.error.message);
               } else {
                   if (typeof data.result == 'boolean') {
                       terminal.echo(data.result ? 'success' : 'fail');
                   } else {
                       var len = data.result.length;
                       for(var i=0;i<len; ++i) {
                           terminal.echo(data.result[i].join(' | '));
                       }
                   }
               }
           },
           function(xhr, status, error) {
               terminal.error('[AJAX] ' + status + 
                          ' - Server reponse is: \n' + 
                          xhr.responseText);
               terminal.resume();
           });*/
    });
});
</script>
SCR;
    $div = '<div id="tilda"></div>';
    $o = str_replace('</body>', $scr.'</body>', $o);
    $o = str_replace('</body>', $div.'</body>', $o);
    
    //заменяем плейсхолдеры
    $page = $modx->makeUrl($modx->documentIdentifier,'','','full');
    $o = str_replace('[+page+]', $page, $o);
  break;
}
//?>