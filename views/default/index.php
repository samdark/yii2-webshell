<?php
/** @var $this yii\web\View */
/** @var $quitUrl string */
/** @var $greetings string */
use yii\helpers\Url;

\samdark\webshell\WebshellAsset::register($this);

$endpoint = Url::toRoute(['default/rpc']);

$this->title = $greetings;

$this->registerJs(
<<<JS
jQuery(function($) {
    var webshell = $('#webshell');

    webshell.terminal(
        function(command, term) {
            if (command.indexOf('yii') === 0 || command.indexOf('yii') === 3) {
                    $.jrpc('{$endpoint}', 'yii', command.replace(/^yii ?/, ''), function(json) {
                        term.echo(json.output);
                        scrollDown();
                    });
            } else if (command === 'help') {
                term.echo('Available commands are:');
                term.echo('');
                term.echo('help\t This help text');
                term.echo('clear\tClear console');
                term.echo('quit\t Quit web shell');
                term.echo('yii [-wp|--runInWebProcess] <command>');
                term.echo('\tcommand \t\t\t\t A yii console command. Type "yii help" to list available commands.');
                term.echo('\t-wp|--runInWebProcess\tRun command in the web application process.');
                term.echo('\t\t\t\t\t\t\t Does not require creating a background process via "popen",');
                term.echo('\t\t\t\t\t\t\t a function which might be disabled in your PHP config.');
                scrollDown();
            } else if (command === 'quit') {
                var exitUrl = '{$quitUrl}';
                if (exitUrl) {
                    term.echo('Bye!');
                    scrollDown();
                    location.replace(exitUrl);
                } else {
                    term.echo('There is no exit.');
                    scrollDown();
                }
            } else {
                term.echo('Unknown command.');
                scrollDown();
            }
        },
        {
            greetings: '$greetings',
            name: 'yii2-webshell',
            prompt: '$ '
        }
    );

    $('html').on('keydown', function(e) {
        webshell.click();
    });

    function scrollDown() {
        $('html, body').animate({ scrollTop: webshell.height() }, 'fast');
    }
});
JS
);
?>
<div id="webshell"></div>
