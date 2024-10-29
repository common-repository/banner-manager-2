<?php

class DN_Calendar
{
  
  private $date_marked;

  public function show($m=1)
  {
    //This gets today's date
    $date = time();

    //This puts the day, month, and year in seperate variables
    $day = date('d', $date);
    $month = $m;
    $year = date('Y', $date);

    //Here we generate the first day of the month
    $first_day = mktime(0,0,0,$month, 1, $year);

    //This gets us the month name
    $title = date('F', $first_day); 
    
    //Here we find out what day of the week the first day of the month falls on 
    $day_of_week = date('D', $first_day) ; 
    
    //Once we know what day of the week it falls on, we know how many blank days occure before it. 
    //If the first day of the week is a Sunday then it would be zero
    switch($day_of_week){ 
      case "Sun": $blank = 0; break; 
      case "Mon": $blank = 1; break; 
      case "Tue": $blank = 2; break; 
      case "Wed": $blank = 3; break; 
      case "Thu": $blank = 4; break; 
      case "Fri": $blank = 5; break; 
      case "Sat": $blank = 6; break; 
    }
    
    //We then determine how many days are in the current month
    $days_in_month = cal_days_in_month(0, $month, $year) ; 
    
    //Here we start building the table heads 

    $retval .= '<div class="cal_title">'.$title.' '.$year.'</div>';

    $retval .= '<div class="cal_days">';
    $retval .= '  <div class="cal_days_i">S</div>';
    $retval .= '  <div class="cal_days_i">M</div>';
    $retval .= '  <div class="cal_days_i">T</div>';
    $retval .= '  <div class="cal_days_i">W</div>';
    $retval .= '  <div class="cal_days_i">T</div>';
    $retval .= '  <div class="cal_days_i">F</div>';
    $retval .= '  <div class="cal_days_i">S</div>';
    $retval .= '</div>';

    //This counts the days in the week, up to 7
    $day_count = 1;

    $retval .= '<div class="cal_date">';
    //first we take care of those blank days
    while ( $blank > 0 ) 
    {
      $retval .= '<div class="cal_date_i_b">&nbsp;</div>'; 
      $blank = $blank-1; 
      $day_count++;
    } 
    
    //sets the first day of the month to 1 
    $day_num = 1;

    //count up the days, untill we've done all of them in the month
    while ( $day_num <= $days_in_month ) 
    { 
      $marker = '';
      if(is_array($this->date_marked['data'])){
        foreach($this->date_marked['data'] as $dm){
          if($m.'-'.$day_num == $dm[0]){
            $marker = $this->date_marked['class'];
          }
        }
      }
      $retval .= '<div class="cal_date_i '.$marker.'" id="'.$m.'-'.$day_num.'"><a href="javascript:void(0);">'.$day_num.'</a></div>'; 
      $day_num++; 
      $day_count++;

      //Make sure we start a new row every week
      if ($day_count > 7)
      {
        $retval .= '</div><div class="cal_date">';
        $day_count = 1;
      }
    }
    
    //Finaly we finish out the table with some blank details if needed
    while ( $day_count >1 && $day_count <=7 ) 
    { 
      $retval .= '<div class="cal_date_i_b">&nbsp;</div>'; 
      $day_count++; 
    } 
    $retval .= '</div>'; 
    
    return $retval;
    
  }
  
  public function show_year(){
    for($i=1;$i<=12;$i++){
      $year_cal .= '<div class="calendar">'.$this->show($i).'</div>';
    }
    return $year_cal;
  }
  
  public function marker($date_mark)
  {
    $this->date_marked = $date_mark;
  }
  
}
?>
