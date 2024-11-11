<?php
include 'config1.php';

$present = 0;
$absent = 0;
$nottaken = 0;
$ttaken = 0;
$strno = $_POST['rollno'];
$absentToday = false;
$currentDate = date("Y-m-d");

// Student data collection
$sql = "SELECT name, sid, rollno FROM student WHERE $strno = rollno";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (count($result)) : ?>

	<?php
	$tempnm = $result[0]['name'];
	$tempid = $result[0]['sid'];
	$rollno = $result[0]['rollno'];
	$lowAttendanceSubjects = []; // Array to store subjects with <= 50% attendance
	?>

	<div class="container">
		<div class="row">
			<div class="col-md-12 col-lg-12">
				<h1 class="page-header"><?php print $tempnm; ?> - <?php print $rollno; ?> Attendance Report</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 col-lg-12">
				<?php
				if ($_POST['student'] === 'y' && isset($_POST['rollno'])) {

					$sq = "SELECT DISTINCT date FROM attendance ORDER BY date";
					$stmt2 = $conn->prepare($sq);
					$stmt2->execute();
					$result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

					// Wrap the table in a scrollable container with a fixed height
					echo "<div style='max-height: 400px; overflow-y: auto;'>";
					echo "<table class='table table-striped table-hover reports-table'>";
					echo "<tr><th>Subject</th>";
					for ($k = 0; $k < count($result2); $k++) {
						$tmdat = $result2[$k]['date'];
						echo "<th>" . date("d-m-Y", $tmdat) . "</th>";
					}

					echo "<th>Total</th><th colspan='2'></th></tr>"; // Removed % from header

					$ssql = "SELECT id FROM student_subject WHERE $tempid = sid";
					$stmt3 = $conn->prepare($ssql);
					$stmt3->execute();
					$result3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

					for ($nosub = 0; $nosub < count($result3); $nosub++) {
						$dpresent = 0;
						$dabsent = 0;
						$dnottaken = 0;
						$dttaken = 0;
						echo "<tr>";
						$subid = $result3[$nosub]['id'];
						$sqql = "SELECT name FROM subject WHERE $subid = id";
						$stmt4 = $conn->prepare($sqql);
						$stmt4->execute();
						$result4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
						$sub = $result4[0]['name'];
						echo "<td><h6>$sub</h6></td>";
						for ($i = 0; $i < count($result2); $i++) {
							$tmdat = $result2[$i]['date'];
							$sql1 = "SELECT ispresent FROM attendance WHERE sid = $tempid AND id = $subid AND date = $tmdat ORDER BY date";
							$stmt1 = $conn->prepare($sql1);
							$stmt1->execute();
							$result1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
							$ttaken++;
							$dttaken++;
							if (empty($result1)) {
								echo " <td><span class='badge' style='background-color:#d44845;'>Not Taken</span></td>";
								$nottaken++;
								$dnottaken++;
							} else {
								$res = $result1[0]['ispresent'];
								if ($res == 1) {
									echo " <td><span class='badge' style='background-color:#3C923C;'>Present</span></td>";
									$present++;
									$dpresent++;
								} else {
									echo "<td><span class='text-danger'>Absent</span></td>";
									$absent++;
									$dabsent++;

									// Check if the absence is for today
									if ($tmdat == strtotime($currentDate)) {
										$absentToday = true;
									}
								}
							}
						}
						$dtlec = $dttaken - $dnottaken;
						if ($dtlec != 0) {
							$dtper = round((100 * $dpresent) / $dtlec, 2);
						} else {
							$dtper = 0;
						}
						echo "<td><strong>" . $dpresent . "</strong>/" . $dtlec . "</td>";
						echo "<td>
                            <div style='width: 100px; background-color: #e0e0e0; border-radius: 5px;'>
                                <div style='width: " . $dtper . "%; background-color: #3C923C; height: 20px; border-radius: 5px;'></div>
                            </div>
                          </td>";
						echo "</tr>";

						// Add subject to low attendance list if percentage is <= 50%
						if ($dtper <= 50) {
							$lowAttendanceSubjects[] = $sub;
						}
					}
					echo "</table>";
					echo "</div>"; // Close the scrollable container

					// Display a single warning if there are subjects with <= 50% attendance
					if (count($lowAttendanceSubjects) > 0) {
						$subjectList = implode(', ', $lowAttendanceSubjects);
						echo '<div style="margin-top: 15px; padding: 10px; background-color: #ffc107; color: #856404; border: 1px solid #ffeeba; border-radius: 5px;">
                        <strong>Warning!</strong> Your attendance for ' . $subjectList . ' is 50% or below. Please attend more classes to improve your attendance.
                    </div>';
					}

					// Alert if absent today
					if ($absentToday) {
						echo "<script>alert('You were absent today. Please make sure to attend upcoming classes.');</script>";
					}
				} else {
					header("location:index.php?student=invalid");
				}
				?>
			</div>
		</div>
	</div>
<?php else: ?>
	<?php header("location:index.php?student=invalid"); ?>
<?php endif; ?>
