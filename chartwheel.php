<?php

  $retrograde = safeEscapeString($_GET["rx"]);

  $longitude[0] = safeEscapeString($_GET["p0"]);
  $longitude[1] = safeEscapeString($_GET["p1"]);
  $longitude[2] = safeEscapeString($_GET["p2"]);
  $longitude[3] = safeEscapeString($_GET["p3"]);
  $longitude[4] = safeEscapeString($_GET["p4"]);
  $longitude[5] = safeEscapeString($_GET["p5"]);
  $longitude[6] = safeEscapeString($_GET["p6"]);
  $longitude[7] = safeEscapeString($_GET["p7"]);
  $longitude[8] = safeEscapeString($_GET["p8"]);
  $longitude[9] = safeEscapeString($_GET["p9"]);

  display_chartwheel($longitude, $retrograde);

  exit();


Function display_chartwheel($longitude, $retrograde)
{
  // set the content-type
  header("Content-type: image/png");


  // create the blank image
  $im = @imagecreatetruecolor(640, 640) or die("Cannot initialize new GD image stream");


  // specify the colors
  $white = imagecolorallocate($im, 255, 255, 255);
  $red = imagecolorallocate($im, 255, 0, 0);
  $blue = imagecolorallocate($im, 0, 0, 255);
  $magenta = imagecolorallocate($im, 255, 0, 255);
  $yellow = imagecolorallocate($im, 255, 255, 0);
  $cyan = imagecolorallocate($im, 0, 255, 255);
  $green = imagecolorallocate($im, 0, 255, 0);
  $grey = imagecolorallocate($im, 127, 127, 127);
  $black = imagecolorallocate($im, 0, 0, 0);
  $lavender = imagecolorallocate($im, 160, 0, 255);

  // specific colors
  $planet_color = $cyan;
  $wheel_color = $yellow;
  $deg_min_color = $white;
  $sign_color = $magenta;

  $size_of_rect = 640;					// size of rectangle in which to draw the wheel
  $diameter = 500;						// diameter of circle drawn
  $radius = $diameter / 2;				// radius of circle drawn
  $center_pt = $size_of_rect / 2;		// center of circle
  $num_planets = 10;
  $max_num_pl_in_each_house = 6;
  $deg_in_each_house = 30;

  // glyphs used for planets - HamburgSymbols.ttf - Sun, Moon - Pluto, Chiron
  $pl_glyph[0] = 81;
  $pl_glyph[1] = 87;
  $pl_glyph[2] = 69;
  $pl_glyph[3] = 82;
  $pl_glyph[4] = 84;
  $pl_glyph[5] = 89;
  $pl_glyph[6] = 85;
  $pl_glyph[7] = 73;
  $pl_glyph[8] = 79;
  $pl_glyph[9] = 80;

  // glyphs used for planets - HamburgSymbols.ttf - Aries - Pisces
  $sign_glyph[1] = 97;
  $sign_glyph[2] = 115;
  $sign_glyph[3] = 100;
  $sign_glyph[4] = 102;
  $sign_glyph[5] = 103;
  $sign_glyph[6] = 104;
  $sign_glyph[7] = 106;
  $sign_glyph[8] = 107;
  $sign_glyph[9] = 108;
  $sign_glyph[10] = 122;
  $sign_glyph[11] = 120;
  $sign_glyph[12] = 99;

  // create black rectangle on blank image
  imagefilledrectangle($im, 0, 0, $size_of_rect, $size_of_rect, $black);


  // MUST BE HERE - I DO NOT KNOW WHY - MAYBE TO PRIME THE PUMP
  imagettftext($im, 10, 0, 0, 0, $black, 'arial.ttf', " ");


  // get variable that tells us if we are in TEST mode - ($in_test = 1), if so
  $in_test = safeEscapeString($_GET["in_test"]);


  // draw the circle of the chartwheel
  imageellipse($im, $center_pt, $center_pt, $diameter, $diameter, $wheel_color);

  // draw the spokes of the
  $spoke_length = 20;
  for ($i = 0; $i <= 355; $i = $i + 5)
  {
    $x1 = -$radius * cos(deg2rad($i));
    $x2 = -$radius * sin(deg2rad($i));

    if (($i % 5 == 0) And ($i % 30 != 0))
    {
      $y1 = -($radius + $spoke_length / 5) * cos(deg2rad($i));
      $y2 = -($radius + $spoke_length / 5) * sin(deg2rad($i));
    }
    else
    {
      $y1 = -($radius + $spoke_length) * cos(deg2rad($i));
      $y2 = -($radius + $spoke_length) * sin(deg2rad($i));
    }

    imageline($im, $x1 + $center_pt, $x2 + $center_pt, $y1 + $center_pt, $y2 + $center_pt, $wheel_color);
  }

  // put signs around chartwheel
  $cw_sign_glyph = 14;
  $ch_sign_glyph = 12;
  $gap_sign_glyph = -60;

  for ($i = 1; $i <= 12; $i++)
  {
    $angle_to_use = deg2rad((($i - 1) * 30) + 15);

    $center_pos_x = -$cw_sign_glyph / 2;
    $center_pos_y = $ch_sign_glyph / 2;

    $offset_pos_x = $center_pos_x * cos($angle_to_use);
    $offset_pos_y = $center_pos_y * sin($angle_to_use);

    $x1 = $center_pos_x + $offset_pos_x + ((-$radius + $gap_sign_glyph) * cos($angle_to_use));
    $y1 = $center_pos_y + $offset_pos_y + (($radius - $gap_sign_glyph) * sin($angle_to_use));

    imagettftext($im, 16, 0, $x1 + $center_pt, $y1 + $center_pt, $sign_color, 'HamburgSymbols.ttf', chr($sign_glyph[$i]));
  }

  // put planets in chartwheel
  // sort longitudes in descending order from 360 down to 0
  Sort_planets_by_descending_longitude($num_planets, $longitude, $sort, $sort_pos);

  // count how many planets are in each house
  Count_planets_in_each_house($num_planets, $sort, $sort_pos, $nopih, $spot_filled);

  $house_num = 0;

  if ($in_test == 0)
  {
    // add planet glyphs around circle
    for ($i = $num_planets - 1; $i >= 0; $i--)
    {
      // $sort() holds longitudes in descending order from 360 down to 0
      // $sort_pos() holds the planet number corresponding to that longitude

      $temp = $house_num;
      $house_num = floor($sort[$i] / 30) + 1;              // get house (sign) planet is in

      if ($temp != $house_num)
      {
        // this planet is in a different house than the last one - this planet is the first one in this house, in other words
        $planets_done = 1;
      }

      // get index for this planet as to where it should be in the possible xx different positions around the wheel
      $from_cusp = Reduce_below_30($sort[$i]);
      if (($from_cusp >= 360 - 1 / 36000) And ($from_cusp <= 360 + 1 / 36000))
      {
        $from_cusp = 0;
      }

      $indexy = floor($from_cusp * $max_num_pl_in_each_house / $deg_in_each_house);

      // adjust the index as needed based on other planets in the same house, etc.
      if ($indexy >= $max_num_pl_in_each_house - $nopih[$house_num])
      {
        if ($max_num_pl_in_each_house - $indexy - $nopih[$house_num] + $planets_done <= 0)
        {
          if ($indexy - $nopih[$house_num] + $planets_done < 0)
          {
            $indexy = $max_num_pl_in_each_house - $nopih[$house_num];
          }
          else
          {
            if ($spot_filled[(($house_num - 1) * $max_num_pl_in_each_house) + $indexy] == 0)
            {
              $indexy = $max_num_pl_in_each_house - $nopih[$house_num] + $planets_done - 1;
            }
            else
            {
              $indexy = $max_num_pl_in_each_house - $nopih[$house_num];
            }
          }
        }

        if ($indexy < 0)
        {
          $indexy = 0;
        }
      }

      // see if this spot around the wheel has already been filled
      while ($spot_filled[(($house_num - 1) * $max_num_pl_in_each_house) + $indexy] == 1)
      {
        // yes, so push the planet up one position
        $indexy++;
      }

      // mark this position as being filled
      $spot_filled[(($house_num - 1) * $max_num_pl_in_each_house) + $indexy] = 1;

      // set the final index
      $chart_idx = ($house_num - 1) * $max_num_pl_in_each_house + $indexy;

      // take the above index and convert it into an angle
      $planet_angle[$sort_pos[$i]] = ($chart_idx * (3 * $deg_in_each_house) / (3 * $max_num_pl_in_each_house)) + ($deg_in_each_house / (2 * $max_num_pl_in_each_house));    // needed for aspect lines
      $angle_to_use = $planet_angle[$sort_pos[$i]];                  // needed for placing info on chartwheel

      // denote that we have done at least one planet in this house (actually count the planets in this house that we have done)
      $planets_done++;

      // display the planet in the wheel
      $angle_to_use = deg2rad($angle_to_use);
      display_planet_glyph($angle_to_use, $radius, $xy);
      imagettftext($im, 16, 0, $xy[0] + $center_pt, $xy[1] + $center_pt, $planet_color, 'HamburgSymbols.ttf', chr($pl_glyph[$sort_pos[$i]]));

      // display degrees of longitude for each planet
      if (mid($retrograde, $sort_pos[$i] + 1, 1) == "R")
      {
        $t = sprintf("%.1f", Reduce_below_30($sort[$i])) . mid($retrograde, $sort_pos[$i] + 1, 1);
      }
      else
      {
        $t = sprintf("%.1f", Reduce_below_30($sort[$i]));
      }

      display_planet_longitude($angle_to_use, $radius, $xy);
      imagettftext($im, 10, 0, $xy[0] + $center_pt, $xy[1] + $center_pt, $deg_min_color, 'arial.ttf', $t);
    }

    // draw in the aspect lines
    for ($i = 0; $i <= $num_planets - 2; $i++)
    {
      for ($j = $i + 1; $j <= $num_planets - 1; $j++)
      {
        $q = 0;
        $da = Abs($longitude[$sort_pos[$i]] - $longitude[$sort_pos[$j]]);

        if ($da > 180)
        {
          $da = 360 - $da;
        }

        // set orb - 8 if Sun or Moon, 6 if not Sun or Moon
        if ($sort_pos[$i] == 0 Or $sort_pos[$i] == 1 Or $sort_pos[$j] == 0 Or $sort_pos[$j] == 1)
        {
          $orb = 8;
        }
        else
        {
          $orb = 6;
        }

        // is there an aspect within orb?
        if ($da <= $orb)
        {
          $q = 1;
        }
        elseif (($da <= (60 + $orb)) And ($da >= (60 - $orb)))
        {
          $q = 6;
        }
        elseif (($da <= (90 + $orb)) And ($da >= (90 - $orb)))
        {
          $q = 4;
        }
        elseif (($da <= (120 + $orb)) And ($da >= (120 - $orb)))
        {
          $q = 3;
        }
        elseif (($da <= (150 + $orb)) And ($da >= (150 - $orb)))
        {
          $q = 5;
        }
        elseif ($da >= (180 - $orb))
        {
          $q = 2;
        }

        if ($q > 0)
        {
          if ($q == 1 Or $q == 3 Or $q == 6)
          {
            $aspect_color = $green;
          }
          elseif ($q == 4 Or $q == 2)
          {
            $aspect_color = $red;
          }
          elseif ($q == 5)
          {
            $aspect_color = $blue;
          }

          if ($q == 1)
          {
            // adjust for weird orientation of imagearc coordinates
            $p1_angle = 180 - $planet_angle[$sort_pos[$i]];
            $p2_angle = 180 - $planet_angle[$sort_pos[$j]];

            // put into range of 0 - 360 degrees
            if ($p1_angle < 0)
            {
              $p1_angle = $p1_angle + 360;
            }

            if ($p2_angle < 0)
            {
              $p2_angle = $p2_angle + 360;
            }

            if (($p1_angle < $p2_angle And ($p2_angle - $p1_angle) < 180) Or ($p1_angle - $p2_angle > 180))
            {
              $start_angle = $p1_angle;
              $end_angle = $p2_angle;
            }
            else
            {
              $start_angle = $p2_angle;
              $end_angle = $p1_angle;
            }

            // 3 lines required to get good thickness
            imagearc($im, $center_pt, $center_pt, $diameter + 4, $diameter + 4, $start_angle, $end_angle, $green);
            imagearc($im, $center_pt, $center_pt, $diameter + 5, $diameter + 5, $start_angle, $end_angle, $green);
            imagearc($im, $center_pt, $center_pt, $diameter + 6, $diameter + 6, $start_angle, $end_angle, $green);
            imagearc($im, $center_pt, $center_pt, $diameter + 7, $diameter + 7, $start_angle, $end_angle, $green);
          }
          else
          {
            $x1 = -$radius * cos(deg2rad($planet_angle[$sort_pos[$i]]));
            $y1 = $radius * sin(deg2rad($planet_angle[$sort_pos[$i]]));
            $x2 = -$radius * cos(deg2rad($planet_angle[$sort_pos[$j]]));
            $y2 = $radius * sin(deg2rad($planet_angle[$sort_pos[$j]]));

            imageline($im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $aspect_color);
          }
        }
      }
    }
  }
  else
  {
    // add planet glyphs around circle
    $step = $deg_in_each_house / $max_num_pl_in_each_house;

    for ($i = 0; $i <= 359.99; $i = $i + $step)
    {
      $angle_to_use = deg2rad($i + ($step / 2));
      display_planet_glyph($angle_to_use, $radius, $xy);
      $glyph = rand(0, 9);
      imagettftext($im, 16, 0, $xy[0] + $center_pt, $xy[1] + $center_pt, $planet_color, 'HamburgSymbols.ttf', chr($pl_glyph[$glyph]));

      // display degrees of longitude for each planet
      $long = Reduce_below_30(rand(0, 359) + (rand(1, 9) / 10));
      if (mid($retrograde, $i + 1, 1) == "R")
      {
        $t = $long . mid($retrograde, $i + 1, 1);
      }
      else
      {
        $t = $long;
      }

      display_planet_longitude($angle_to_use, $radius, $xy);
      imagettftext($im, 10, 0, $xy[0] + $center_pt, $xy[1] + $center_pt, $deg_min_color, 'arial.ttf', $t);
    }
  }


  // draw the image in png format - using imagepng() results in clearer text compared with imagejpeg()
  imagepng($im);
  imagedestroy($im);
}

Function mid($midstring, $midstart, $midlength)
{
  return(substr($midstring, $midstart-1, $midlength));
}

Function Reduce_below_30($longitude)
{
  $lng = $longitude;

  while ($lng >= 30)
  {
    $lng = $lng - 30;
  }

  return $lng;
}

Function safeEscapeString($string)
{
// replace HTML tags '<>' with '[]'
  $temp1 = str_replace("<", "[", $string);
  $temp2 = str_replace(">", "]", $temp1);

// but keep <br> or <br />
// turn <br> into <br /> so later it will be turned into ""
// using just <br> will add extra blank lines
  $temp1 = str_replace("[br]", "<br />", $temp2);
  $temp2 = str_replace("[br /]", "<br />", $temp1);

  if (get_magic_quotes_gpc())
  {
    return $temp2;
  }
  else
  {
    return mysql_escape_string($temp2);
  }
}

Function Sort_planets_by_descending_longitude($num_planets, $longitude, &$sort, &$sort_pos)
{
  // load all $longitude() into sort() and keep track of the planet numbers in $sort_pos()
  for ($i = 0; $i <= $num_planets - 1; $i++)
  {
    $sort[$i] = $longitude[$i];
    $sort_pos[$i] = $i;
  }

  // do the actual sort
  for ($i = 0; $i <= $num_planets - 2; $i++)
  {
    for ($j = $i + 1; $j <= $num_planets - 1; $j++)
    {
      if ($sort[$j] > $sort[$i])
      {
        $temp = $sort[$i];
        $temp1 = $sort_pos[$i];

        $sort[$i] = $sort[$j];
        $sort_pos[$i] = $sort_pos[$j];

        $sort[$j] = $temp;
        $sort_pos[$j] = $temp1;
      }
    }
  }
}

Function Count_planets_in_each_house($num_planets, $sort, $sort_pos, &$nopih, &$spot_filled)
{
  // count the number of planets in each house
  // unset any variables not initialized elsewhere in the program
  // reset the number of planets in each house
  // make $spot_filled times 15 (instead of 12) just to be sure (to cover overflow)
  unset($spot_filled);

  for ($i = 1; $i <= 12; $i++)
  {
    $nopih[$i] = 0;
  }

  // run through all the planets and see how many planets are in each house
  for ($i = 0; $i <= $num_planets - 1; $i++)
  {
    // get sign planet is in, since the sign and the house are the same
    $p_num = $sort_pos[$i];
    $temp = floor($sort[$p_num] / 30) + 1;
    $nopih[$temp]++;
  }
}

Function display_planet_glyph($angle_to_use, $radii, &$xy)
{
  $cw_pl_glyph = 16;
  $ch_pl_glyph = 16;
  $gap_pl_glyph = -10;

  // take into account the width and height of the glyph, defined below
  // get distance we need to shift the glyph so that the absolute middle of the glyph is the start point
  $center_pos_x = -$cw_pl_glyph / 2;
  $center_pos_y = $ch_pl_glyph / 2;

  // get the offset we have to move the center point to in order to be properly placed
  $offset_pos_x = $center_pos_x * cos($angle_to_use);
  $offset_pos_y = $center_pos_y * sin($angle_to_use);

  // now get the final X, Y coordinates
  $xy[0] = $center_pos_x + $offset_pos_x + ((-$radii + $gap_pl_glyph) * cos($angle_to_use));
  $xy[1] = $center_pos_y + $offset_pos_y + (($radii - $gap_pl_glyph) * sin($angle_to_use));

  return ($xy);
}

Function display_planet_longitude($angle_to_use, $radii, &$xy)
{
  $cw_deg_min = 18;
  $ch_deg_min = 10;
  $gap_deg_min = -45;

  // take into account the width and height of the deg/min, defined below
  // get distance we need to shift the deg/min so that the absolute middle of the deg/min is the start point
  $center_pos_x = -$cw_deg_min / 2;
  $center_pos_y = $ch_deg_min / 2;

  // get the offset we have to move the center point to in order to be properly placed
  $offset_pos_x = center_pos_x * cos($angle_to_use);
  $offset_pos_y = center_pos_y * sin($angle_to_use);

  // now get the final X, Y coordinates
  $xy[0] = $center_pos_x + $offset_pos_x + ((-$radii + $gap_deg_min) * cos($angle_to_use));
  $xy[1] = $center_pos_y + $offset_pos_y + (($radii - $gap_deg_min) * sin($angle_to_use));

  return ($xy);
}

?>
