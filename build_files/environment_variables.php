<?php
/**
 * Environment Variables Generation Script
 * 
 * This script is use for generating environment from bitbucket pipelines
 * to .env file. To produce the file correctly, the script should do the following:
 * 
 * 1.) read .env.example   and write to .env file
 * 2.) load .env file and replace variable with the bitbucket deployment variables
 */


/**
 * Remove .env file and replace with the .env.example content
 * 
 * This is to ensure that all variables have been reset before adding a new one
 * to avoid unexpected concatenated variable strings
 */
function clearEnv () {
  exec("cp -rf .env.example .env", $out, $status);
  # return 1 if successfully created otherwise return 0 and throw error message
  if($status === 0) return 1;
  echo 'Unable to copy env file';
  return $status;
}


/**
 * Check if env file exists and create otherwise
 */
 function copyExampleEnvFile () {
   # do not remove old env
    if(file_exists('.env')) return 1;
    # create new file
    clearEnv ();
 }

/**
 * Load environment variables to memory after creating .env
 * 
 * This will replace the initial variables with the epecified key and value
 * <string> $key
 * <string> $value
 * @example APP_URL , http://localhost
 */
 function readEnvFile ($key, $val) {
  $is_written_env_file = copyExampleEnvFile();
  if(!$is_written_env_file) return 0;

  $env_file = (string) file_get_contents('.env');
  $updated_env = $env_file;

  # update environment variables
  return str_replace($key.'=', $key.'='.$val, $updated_env);
 }

/**
 * Write environment variables from memory to file
 * 
 * This will update the .env file accordingly
 * Note: make sure that you have a RW permission before running this command
 */
 function writeUpdatedEnvFile ($key, $val) {
  file_put_contents('.env', readEnvFile($key, $val));
  return $val;
 }

 # execute script
 # get cli commands for adding and clearing env
 $env = (explode('=',$argv[1]));

 # Reset all environment variables to default as specified in .env.example
 # to use this command, run the script below
 # > php environment_variables clear
 if($argv[1] === 'clear') return clearEnv();


 # add new variable
 # > php environment_variables APP_URL=http://localhost
 if(!isset($env[1])) echo 'No environment variable was added';
 echo writeUpdatedEnvFile($env[0], $env[1]);
 # add delay
 sleep(1);