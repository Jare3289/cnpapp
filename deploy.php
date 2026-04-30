<?php
/**
 * Advanced Auto-Deploy Script with Progress Bar
 */

// 1. Connection Details
$ftp_host = 'ftpupload.net';
$ftp_user = 'if0_41789362';
$ftp_pass = 'ik47Gv2CMCT1';

// DB Config (Placeholder - Replace if known)
$mysql_host = 'sql308.infinityfree.com';
$mysql_db   = 'if0_41789362_cnpapp'; 
$mysql_user = 'if0_41789362';
$mysql_pass = 'ik47Gv2CMCT1';

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>CNP Deployment Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .deploy-card { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-top: 50px; }
        .progress { height: 30px; border-radius: 15px; margin: 20px 0; }
        #log { background: #1e293b; color: #10b981; padding: 20px; border-radius: 10px; font-family: monospace; height: 300px; overflow-y: auto; font-size: 0.8rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="card deploy-card">
        <div class="card-body p-5">
            <h2 class="fw-bold text-center mb-4">🚀 CNP APP Deployment System</h2>
            
            <?php
            if (isset($_GET['start'])) {
                // Disable output buffering
                if (ob_get_level()) ob_end_clean();
                echo '<div class="progress"><div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%">0%</div></div>';
                echo '<p id="status-text" class="text-muted small">Initializing...</p>';
                echo '<div id="log">';
                
                set_time_limit(0);
                
                // Count files first
                function countFiles(string $dir): int {
                    $count = 0;
                    $files = scandir($dir);
                    foreach ($files as $file) {
                        if ($file == '.' || $file == '..' || $file == '.git' || $file == 'node_modules' || $file == 'deploy.php' || $file == 'cnpapp_export.sql') continue;
                        if (is_dir($dir . '/' . $file)) $count += countFiles($dir . '/' . $file);
                        else $count++;
                    }
                    return $count;
                }
                
                $totalFiles = countFiles(__DIR__);
                $uploadedCount = 0;

                $conn_id = ftp_connect($ftp_host);
                if (!$conn_id || !ftp_login($conn_id, $ftp_user, $ftp_pass)) {
                    echo "❌ FTP Login Failed!</div>";
                    die();
                }
                ftp_pasv($conn_id, true);

                function updateProgress(int $current, int $total): void {
                    $percent = round(($current / $total) * 100);
                    echo "<script>
                        document.getElementById('progress-bar').style.width = '$percent%';
                        document.getElementById('progress-bar').innerHTML = '$percent%';
                        document.getElementById('status-text').innerHTML = 'Uploading $current of $total files...';
                    </script>";
                    flush();
                }

                function uploadDir(mixed $conn_id, string $localDir, string $remoteDir, int &$uploadedCount, int $totalFiles): void {
                    @ftp_mkdir($conn_id, $remoteDir);
                    $files = scandir($localDir);
                    foreach ($files as $file) {
                        if ($file == '.' || $file == '..' || $file == '.git' || $file == 'node_modules' || $file == 'deploy.php' || $file == 'cnpapp_export.sql') continue;
                        
                        $localPath = $localDir . '/' . $file;
                        $remotePath = $remoteDir . '/' . $file;

                        if (is_dir($localPath)) {
                            uploadDir($conn_id, $localPath, $remotePath, $uploadedCount, $totalFiles);
                        } else {
                            if (ftp_put($conn_id, $remotePath, $localPath, FTP_BINARY)) {
                                $uploadedCount++;
                                updateProgress($uploadedCount, $totalFiles);
                                echo "Uploaded: $remotePath <br>";
                            } else {
                                echo "<span style='color:red;'>Failed: $remotePath</span> <br>";
                            }
                            flush();
                        }
                    }
                }

                uploadDir($conn_id, __DIR__, 'htdocs', $uploadedCount, $totalFiles);

                // Config upload
                $config_content = "<?php
                \$host = '$mysql_host';
                \$db   = '$mysql_db';
                \$user = '$mysql_user';
                \$pass = '$mysql_pass';
                \$charset = 'utf8mb4';
                \$dsn = \"mysql:host=\$host;dbname=\$db;charset=\$charset\";
                \$options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                try { \$pdo = new PDO(\$dsn, \$user, \$pass, \$options); } 
                catch (\\PDOException \$e) { die(\"DB Error: \" . \$e->getMessage()); }
                ?>";
                $temp_config = tempnam(sys_get_temp_dir(), 'config');
                file_put_contents($temp_config, $config_content);
                ftp_put($conn_id, 'htdocs/config.php', $temp_config, FTP_BINARY);
                
                echo "</div>"; // Close log
                echo "<h3 class='text-success text-center mt-4'>✅ DEPLOYMENT COMPLETE!</h3>";
                echo "<div class='text-center mt-3'><a href='http://cnpapp.rf.gd' class='btn btn-lg btn-primary rounded-pill'>GO TO WEBSITE</a></div>";
                
                ftp_close($conn_id);
            } else {
                echo '<div class="text-center">
                        <p class="mb-4">พร้อมส่งไฟล์ไปที่ <b>cnpapp.rf.gd</b> (ข้าม node_modules แล้ว)</p>
                        <a href="?start=1" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold">PUSH TO CLOUD SERVER</a>
                      </div>';
            }
            ?>
        </div>
    </div>
</div>
<script>
    const log = document.getElementById('log');
    if(log) {
        const observer = new MutationObserver(() => {
            log.scrollTop = log.scrollHeight;
        });
        observer.observe(log, { childList: true });
    }
</script>
</body>
</html>
