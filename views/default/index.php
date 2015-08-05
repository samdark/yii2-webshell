<?php
/** @var yii\web\View $this */
?>
<div id="webshell"></div>

<?php
$this->registerJs(
<<<JS
jQuery(function($, undefined) {
    $('#webshell').terminal(function(command, term) {
        if (command !== '') {
            var result = window.eval(command);
            if (result != undefined) {
                term.echo(String(result));
            }
        }
    }, {
        greetings: 'Javascript Interpreter',
        name: 'js_demo',
        height: 200,
        width: 450,
        prompt: 'js> '});
});
JS
);
?>

