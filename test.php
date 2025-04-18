<?php
$program = '[{"id":1,"day":"2024-10-22","time":"11:59","stat":"not"},{"id":2,"day":"2024-10-31","time":"11:59","stat":"not"}]';
$sub_program_id = 1;
if($program) {
  $programs = json_decode($program, true);
  if($programs) {
    foreach($programs as $key => $v) {
      if($v['id'] == $sub_program_id) {
        $v['stat'] = "passed";
        $programs[$key]['stat'] = "passed";
      }
    }
  }
  $program = json_encode($programs);
}
var_dump($program);
?>
