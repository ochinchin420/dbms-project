<?php include 'config1.php';?>
<?php
	//echo"Take a view here";
	$suid = $_SESSION['uid'];
	//echo $suid;
?>

<div class="container">
  <div class="row">
    <div class="col-md-12 col-lg-12">
			<h1 class="page-header">Reports</h1>  
		</div>
	</div>

	<div class="row text-center">
		<div class="col-md-12 col-lg-12">
			<form action="" method="GET" class="form-inline" data-toggle="validator">
				<div class="form-group ">
					<label for="select" class="control-label">Subject:</label>
					<?php
						$query_subject = "SELECT subject.name, subject.id from subject 
					INNER JOIN user_subject WHERE user_subject.id = subject.id AND user_subject.uid = $suid  ORDER BY subject.name";
						$sub=$conn->query($query_subject);
						$rsub=$sub->fetchAll(PDO::FETCH_ASSOC);
						//print_r($rsub);
						$subnm=$rsub[0]['name'];
						$subid=$rsub[0]['id'];
						//echo "<h3>".$subnm." ".$subid."</h3>";
					
						echo "<select name='subject' class='form-control' required='required'>";
						for($i = 0; $i<count($rsub); $i++)
						{
							if ($_GET['subject'] == $rsub[$i]['id']) {
								echo"<option value='". $rsub[$i]['id']."' selected='selected'>".$rsub[$i]['name']."</option>";
							}
							else {
								echo"<option value='". $rsub[$i]['id']."'>".$rsub[$i]['name']."</option>";
							}
						}
						echo "</select><br>";
					?>
				</div>
				
				<div class="form-group" data-provide="datepicker">
					<label for="select" class="control-label">From:</label>
					<input type="date" name="sdate" class="form-control" value="<?php print isset($_GET['sdate']) ? $_GET['sdate'] : ''; ?>" required>
				</div>
				
				<div class="form-group" data-provide="datepicker">
					<label for="select" class="control-label">To:</label>
					<input type="date" name="edate" class="form-control" value="<?php print isset($_GET['edate']) ? $_GET['edate'] : ''; ?>" required>
				</div>
				
				<input type="hidden" name="page" value="reports">
				<button type="submit" class="btn btn-danger" name="submit" style='border-radius:0%;'><i class="glyphicon glyphicon-filter"></i> Filter Student</button>
			</form>
		</div>	
	</div>
</div>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
		
			<p>&nbsp;</p>
			<div class="report-data">
			
			
			<?php

				
				$t=time();

				if(isset($_GET['submit']) && !empty($_GET['sdate']) && !empty($_GET['edate']) && ($_GET['edate'] > $_GET['sdate']) && (strtotime($_GET['sdate'])<=$t) && (strtotime($_GET['edate'])<=$t))
				{
					$sdat = $_GET['sdate'];
					$edat= $_GET['edate'];

					$selsub=$_GET['subject'];
					
					$sdate = strtotime($sdat);
					$edate = strtotime($edat);

                    if(($sdate<$t) && ($edate<=$t) && ($edate >= $sdate))
                    {
                        
                        // echo "sub id".$selsub."<br>";
                        // echo "user id".$suid."<br>";
                        // echo "starting date:".$sdat." "."ending date:".$edat."<br>";
                        // $query_student="SELECT * from student ";
                        $query_student = "SELECT student.sid, student.name, student.rollno from student INNER JOIN student_subject WHERE student.sid = student_subject.sid AND student_subject.id  = $selsub  ORDER BY student.sid";
                        $stu=$conn->query($query_student);
                        $rstu=$stu->fetchAll(PDO::FETCH_ASSOC);
                        //	print_r($rstu);
                        //	echo "<br><br>--------------<br>";
                        echo "<table class='table table-striped table-hover reports-table'>";
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th>Roll</th>";
                        echo "<th>Student's Name</th>";
                        for($k=$sdate;$k<=$edate;$k=$k+86400)
                        {
                            $thisDate = date( 'd-m-Y', $k );
                            $weekday= date("l", $k );
                            $normalized_weekday = strtolower($weekday);
                            if(($normalized_weekday!="saturday") && ($normalized_weekday!="sunday"))
                            {
                                echo "<th>".$thisDate."</th>";
                            }
                        }
                        echo "<th>Present/Total</th>";
                        echo "<th>Precentage</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";
                        for($i=0;$i<count($rstu);$i++)
                        {
                            //echo $i."--"."<br>";
                            $present=0;
                            $absent=0;
                            $totlec=0;
                            $perc=0;
                            echo"<tr><td><h6>".$rstu[$i]['rollno']."</h6></td>";
                            echo "<td><h5>".$rstu[$i]['name']."</h5></td>";
                            $dsid=$rstu[$i]['sid'];
                            
                            for ($j = $sdate; $j <= $edate; $j += 86400) {
                                $weekday = date("l", $j);
                                $currentDate = date('Y-m-d', $j);
                                $normalized_weekday = strtolower($weekday);
                                
                                if ($normalized_weekday != "saturday" && $normalized_weekday != "sunday") {
                                    // Modify the SQL query to select attendance for the specific student and date
                                    $sql = "SELECT ispresent FROM attendance WHERE id = :subjectId AND sid = :studentId AND date>=($j-86400) AND date<=$j";
                                    $stmt = $conn->prepare($sql); 
                                    $stmt->execute([
                                        ':subjectId' => $selsub,
                                        ':studentId' => $dsid
                                    ]);
                                    $result = $stmt->fetch(PDO::FETCH_ASSOC); 
                                    
                                    if (!empty($result)) {
                                        $totlec++;
                                        if ($result['ispresent'] == 1) {
                                            $present++;
                                            echo "<td><span class='text-success'>Present</span></td>";
                                        } else {
                                            $absent++;
                                            echo "<td><span class='text-danger'>Absent</span></td>";
                                        }
                                    } else {
                                        echo "<td><a href='index.php?subject=" . $selsub . "&date=" . $currentDate . "'><button type='button' class='btn btn-success btn-sm' style='border-radius:0%'>Take Attendance</button></a></td>";
                                    }
                                }
                            }
                            if($totlec!=0)
                                $perc=round((($present*100)/$totlec), 2);
                            else
                                $perc=0;
                            echo"<td><strong>".$present."</strong>/".$totlec."</td>";
                            echo"<td>".$perc."&nbsp;%</td>";
                            echo"</tr>";
                            
                        }		
                        echo "</tbody>";
                        echo "</table>";
                    }else
                    {
                        echo '<div class="alert alert-dismissible alert-danger">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong>Sorry!</strong>Please enter correct date range.
                            </div>';
                    }

				}else{
					 // echo"<h3>Please enter detail</h3>";
				}



			?>
			</div>
		</div>
	</div>
</div>