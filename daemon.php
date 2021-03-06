<?php
/**
 * @author Алексей Пастушенко
 * bigguest@gmail.com
 */

namespace Pav\Daemon;

spl_autoload_register(function ($class) {
    $class = str_replace(__NAMESPACE__.'\\','',$class);
    if (strstr($class, 'Interface')) {
        require dirname(__FILE__) . "/Interfaces/{$class}.php";
    } elseif (strstr($class, 'Trait')) {
        require dirname(__FILE__) . "/Traits/{$class}.php";
    } else {
        require dirname(__FILE__) . "/Classes/{$class}.php";
    }
});

Log::$debug = true;

$child_pid = pcntl_fork();
if ($child_pid) {
    // Выходим из родительского, привязанного к консоли, процесса
    exit();
}
// Делаем основным процессом дочерний.
posix_setsid();

$baseDir = dirname(__FILE__);
ini_set('error_log', $baseDir . '/error.log');
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($baseDir . '/application.log', 'ab');
$STDERR = fopen($baseDir . '/daemon.log', 'ab');

/*Поддержка реагирования на сигналы*/
declare(ticks=1);
pcntl_signal_dispatch();

$daemon = new DaemonClass(
    new WorkerConfig(
        function ($config) {
            /*@todo тело демона, выполняющее полезную работу*/
            var_dump($config);

            gc_collect_cycles();
            exit();
        }
    )
);

/*запускаем обработку очереди, вызовом метода run c предачей элемента очереди*/
foreach ([1, 2, 3, 4, 5, 6, 7, 8] as $source) {
    $daemon->run($source);
}

Log::w(time() . ": Все работы завершены");
