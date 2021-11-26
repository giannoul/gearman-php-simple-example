<?php

# create the gearman client
$gmc= new GearmanClient();

# Add default server (localhost).
try {
    $gmc->addServer();
    //If the exception is thrown, this text will not be shown
    echo 'Connecting to localhost gearman-server';
}
//catch exception
catch(Exception $e) {
    echo '(localhost-network) Message: ' .$e->getMessage();
}
  
try {
    $gmc->addServer("gearman-server", 4730);
    //If the exception is thrown, this text will not be shown
    echo 'Connecting to gearman-server in docker-compose network';
}
//catch exception
catch(Exception $e) {
    echo '(docker-compose-network) Message: ' .$e->getMessage();
}

# register some callbacks
$gmc->setCreatedCallback("reverse_created");
$gmc->setDataCallback("reverse_data");
$gmc->setStatusCallback("reverse_status");
$gmc->setCompleteCallback("reverse_complete");
$gmc->setFailCallback("reverse_fail");

# set some arbitrary application data
$data['foo'] = 'bar';

# add two tasks
$task= $gmc->addTask("reverse", "foo", $data);
$task2= $gmc->addTaskLow("reverse", "bar", NULL);
$task2= $gmc->addTaskLow("reverse", "Ineedsomethingmuchmorebiggerinordertotest", NULL);


# run the tasks in parallel (assuming multiple workers)
if (! $gmc->runTasks())
{
    echo "ERROR " . $gmc->error() . "\n";
    exit;
}

echo "DONE\n";

function reverse_created($task)
{
    echo "CREATED: " . $task->jobHandle() . "\n";
}

function reverse_status($task)
{
    echo "STATUS: " . $task->jobHandle() . " - " . $task->taskNumerator() . 
         "/" . $task->taskDenominator() . "\n";
}

function reverse_complete($task)
{
    echo "COMPLETE: " . $task->jobHandle() . ", " . $task->data() . "\n";
}

function reverse_fail($task)
{
    echo "FAILED: " . $task->jobHandle() . "\n";
}

function reverse_data($task)
{
    echo "DATA: " . $task->data() . "\n";
}

?>