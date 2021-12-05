<?php

/*
  Handle SIGINT signal
*/
declare(ticks = 1);
$terminate = false;

//pcntl_signal(SIGQUIT, function () use (&$terminate) { echo "Received SIGQUIT signal. Will stop receiving new jobs.\n";$terminate = true; });
// signal handler function
function sig_handler($signo)
{
     global $terminate;
     switch ($signo) {
         case SIGTERM:
             echo "Caught SIGTERM...\n";
             //exit;
             $terminate = true;
             break;
         case SIGHUP:
             echo "Caught SIGHUP...\n";
             $terminate = true;
             break;
         case SIGUSR1:
             echo "Caught SIGUSR1...\n";
             $terminate = true;
             break;
         default:
            echo "Got signal $signo but I don't know how to handle it \n";
     }

}

echo "Installing signal handler...\n";

// setup signal handlers
pcntl_signal(SIGTERM, "sig_handler");
pcntl_signal(SIGHUP,  "sig_handler");
pcntl_signal(SIGUSR1, "sig_handler");
pcntl_signal(SIGINT, "sig_handler");

// or use an object
// pcntl_signal(SIGUSR1, array($obj, "do_something"));

//echo"Generating signal SIGUSR1 to self...\n";

// send SIGUSR1 to current process id
// posix_* functions require the posix extension
//echo posix_getpid();
//posix_kill(posix_getpid(), SIGUSR1);




echo "Starting\n";

# Create our worker object.
$gmworker= new GearmanWorker();

# Make the worker non-blocking
$gmworker->addOptions(GEARMAN_WORKER_NON_BLOCKING);
$gmworker->setTimeout(1000);

# Add default server (localhost).
try {
  $gmworker->addServer();
  //If the exception is thrown, this text will not be shown
  echo 'Connecting to localhost gearman-server';
}
//catch exception
catch(Exception $e) {
  echo '(localhost-network) Message: ' .$e->getMessage().'\n';
}

try {
  $gmworker->addServer("gearman-server", 4730);
  //If the exception is thrown, this text will not be shown
  echo 'Connecting to gearman-server in docker-compose network\n';
}
//catch exception
catch(Exception $e) {
  echo '(docker-compose-network) Message: ' .$e->getMessage().'\n';
}


# Register function "reverse" with the server. Change the worker function to
# "reverse_fn_fast" for a faster worker with no output.
$gmworker->addFunction("reverse", "reverse_fn");

print "Waiting for job...\n";
while( (!$terminate) && ($gmworker->work()||
  $gmworker->returnCode() == GEARMAN_IO_WAIT ||
  $gmworker->returnCode() == GEARMAN_NO_JOBS ||
  $gmworker->returnCode() == GEARMAN_TIMEOUT) )
{
  if ($gmworker->returnCode() == GEARMAN_SUCCESS) {
    continue;
  }

  echo "[ " . date("Y-m-d H:i:s") . " ] Waiting for next job...\n";
  if (!$gmworker->wait())
  {
    if ($gmworker->returnCode() == GEARMAN_NO_ACTIVE_FDS)
    {
      # We are not connected to any servers, so wait a bit before
      # trying to reconnect.
       sleep(5);
       continue;
    }
    elseif ($gmworker->returnCode() == GEARMAN_TIMEOUT)
    {
       echo "Timedout. Retrying \n";
       sleep(1);
       continue;
    }


    break;
  } 
  echo "return code " . $gmworker->returnCode() . "\n";

  echo "-----------\n";
}

function reverse_fn($job)
{
  echo "Received job: " . $job->handle() . "\n";

  $workload = $job->workload();
  $workload_size = $job->workloadSize();

  echo "Workload: $workload ($workload_size)\n";

  # This status loop is not needed, just showing how it works
  for ($x= 0; $x < $workload_size; $x++)
  {
    echo "Sending status: " . ($x + 1) . "/$workload_size complete\n";
    $job->sendStatus($x+1, $workload_size);
    $job->sendData(substr($workload, $x, 1));
    echo "--------------\n";
    echo memory_get_usage()."\n";
    echo "--------------\n";
    sleep(1);
  }

  $result= strrev($workload);
  echo "Result: $result\n";

  # Return what we want to send back to the client.
  return $result;
}

# A much simpler and less verbose version of the above function would be:
function reverse_fn_fast($job)
{
  return strrev($job->workload());
}

?>