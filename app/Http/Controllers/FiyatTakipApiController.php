<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class FiyatTakipApiController extends Controller
{
    /**
     * Fiyat takip işlemini başlatır
     */
    public function start(Request $request)
    {
        Log::info('Fiyat takibi başlatma isteği alındı', ['data' => $request->all()]);

        $validated = $request->validate([
            'products' => 'required|array',
            'email' => 'required|email',
            'appPassword' => 'required|string'
        ]);

        // Gelen verileri geçici bir JSON dosyasına kaydet
        $configData = [
            'email' => $validated['email'],
            'app_password' => $validated['appPassword'],
            'products' => $validated['products'],
            'check_interval' => (int) env('FIYAT_TAKIP_CHECK_INTERVAL', 600) // 10 dakika varsayılan
        ];
        
        // Tam yollar oluştur - burada absolute path kullanacağız
        $configPath = storage_path('app/fiyat_takip_config.json');
        $logPath = storage_path('logs/price-tracker.log');
        $pythonPath = base_path('python/price-tracker.py');
        $pidPath = storage_path('app/fiyat_takip_pid.txt');
        
        try {
            // Debug bilgilerini logla
            Log::info('Dosya yolları kontrol ediliyor', [
                'storage_path' => storage_path(),
                'base_path' => base_path(),
                'config_path' => $configPath
            ]);
            
            // Config dizininin var olduğundan emin ol
            $configDir = dirname($configPath);
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
                Log::info('Konfigürasyon dizini oluşturuldu', ['path' => $configDir]);
            }
            
            // Doğrudan file_put_contents kullanarak dosyayı oluştur
            file_put_contents($configPath, json_encode($configData, JSON_PRETTY_PRINT));
            
            Log::info('Konfigürasyon dosyası oluşturuldu', ['path' => $configPath]);
            
            // Dosyanın oluşturulduğunu kontrol et
            if (!file_exists($configPath)) {
                Log::error('Konfigürasyon dosyası oluşturulamadı', ['path' => $configPath]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Konfigürasyon dosyası oluşturulamadı'
                ], 500);
            }
            
            // Log klasörünün var olduğundan emin ol
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
                Log::info('Log dizini oluşturuldu', ['path' => $logDir]);
            }
            
            // Log dosyasının var olduğundan emin ol
            if (!file_exists($logPath)) {
                touch($logPath);
                chmod($logPath, 0777); // Yazma izinlerini ayarla
                Log::info('Log dosyası oluşturuldu', ['path' => $logPath]);
            }

            // Dosyaların mutlak yollarını al
            $absoluteConfigPath = realpath($configPath);
            $absoluteLogPath = realpath($logPath);
            $absolutePythonPath = realpath($pythonPath);
            $absoluteBasePath = realpath(base_path());
            
            // Mutlak yolları kontrol et
            if (!$absoluteConfigPath) {
                Log::error('Konfigürasyon dosyasının mutlak yolu alınamadı', ['path' => $configPath]);
                $absoluteConfigPath = $configPath; // Varsayılan olarak tekrar dene
            }
            
            if (!$absoluteLogPath) {
                Log::error('Log dosyasının mutlak yolu alınamadı', ['path' => $logPath]);
                $absoluteLogPath = $logPath; // Varsayılan olarak tekrar dene
            }
            
            if (!$absolutePythonPath) {
                Log::error('Python betiğinin mutlak yolu alınamadı', ['path' => $pythonPath]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Python betiği bulunamadı'
                ], 500);
            }

            // Mevcut bir Python süreci var mı kontrol et
            if (file_exists($pidPath)) {
                $pid = trim(file_get_contents($pidPath));
                if ($this->isProcessRunning($pid)) {
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'Fiyat takibi zaten çalışıyor',
                        'pid' => $pid
                    ]);
                } else {
                    // PID dosyası var ama process yok, dosyayı temizle
                    unlink($pidPath);
                }
            }

            // Arka planda çalıştırma komutu oluştur
            $cmd = sprintf(
                'nohup python3 %s --config=%s --log=%s --base-dir=%s > /dev/null 2>&1 & echo $!',
                escapeshellarg($absolutePythonPath),
                escapeshellarg($absoluteConfigPath),
                escapeshellarg($absoluteLogPath),
                escapeshellarg($absoluteBasePath)
            );
            
            Log::info('Arka planda çalıştırma komutu:', ['cmd' => $cmd]);
            
            // Komutu çalıştır ve PID'yi al
            $pid = trim(shell_exec($cmd));
            
            // PID geçerli mi kontrol et
            if (!is_numeric($pid)) {
                Log::error('Geçersiz PID alındı', ['pid' => $pid]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Python süreci başlatılamadı'
                ], 500);
            }
            
            // PID'yi kaydet
            file_put_contents($pidPath, $pid);
            
            Log::info('Fiyat takibi başlatıldı', ['pid' => $pid]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Fiyat takibi başlatıldı',
                'pid' => $pid
            ]);
        } catch (\Exception $e) {
            Log::error('Fiyat takibi başlatma hatası: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Fiyat takibi başlatılamadı: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process'in çalışıp çalışmadığını kontrol eder
     */
    private function isProcessRunning($pid) {
        if (!$pid) return false;
        
        // Komut çalışma durumunu döndürür
        if (PHP_OS_FAMILY === 'Darwin') {
            // macOS için
            exec("ps -p $pid", $output, $returnCode);
        } else {
            // Linux için
            exec("ps -p $pid -o pid=", $output, $returnCode);
        }
        
        return $returnCode === 0;
    }

    /**
     * Fiyat takip işlemini durdurur
     */
    public function stop()
    {
        $pidFile = storage_path('app/fiyat_takip_pid.txt');
        
        if (file_exists($pidFile)) {
            $pid = trim(file_get_contents($pidFile));
            Log::info('Fiyat takibi durdurma isteği alındı', ['pid' => $pid]);
            
            try {
                // Süreç gerçekten çalışıyor mu kontrol et
                if (!$this->isProcessRunning($pid)) {
                    Log::warning('PID dosyası mevcut ancak süreç çalışmıyor', ['pid' => $pid]);
                    unlink($pidFile);
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'Çalışan bir fiyat takibi bulunamadı'
                    ]);
                }
                
                // Süreci durdur (SIGTERM sinyali)
                exec("kill $pid", $output, $returnCode);
                
                $result = $returnCode === 0 ? 'başarılı' : 'başarısız';
                Log::info('Process durdurma sonucu: ' . $result, ['output' => $output]);
                
                // PID dosyasını sil
                unlink($pidFile);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Fiyat takibi durduruldu'
                ]);
            } catch (\Exception $e) {
                Log::error('Fiyat takibi durdurma hatası: ' . $e->getMessage());
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Fiyat takibi durdurulurken hata oluştu: ' . $e->getMessage()
                ], 500);
            }
        }
        
        Log::warning('Çalışan bir fiyat takibi bulunamadı');
        return response()->json([
            'status' => 'warning',
            'message' => 'Çalışan bir fiyat takibi bulunamadı'
        ]);
    }

    /**
     * Fiyat takip durumunu döndürür
     */
    public function getStatus()
    {
        $pidFile = storage_path('app/fiyat_takip_pid.txt');
        $logFile = storage_path('logs/price-tracker.log');
        
        $pid = null;
        $isRunning = false;
        
        if (file_exists($pidFile)) {
            $pid = trim(file_get_contents($pidFile));
            $isRunning = $this->isProcessRunning($pid);
            
            if (!$isRunning) {
                // Process çalışmıyor ama PID dosyası var, temizlik yap
                unlink($pidFile);
            }
        }
        
        $lastLogLines = [];
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            $logLines = explode("\n", $logContent);
            $lastLogLines = array_slice($logLines, -20); // Son 20 satır
        }
        
        return response()->json([
            'isRunning' => $isRunning,
            'pid' => $pid,
            'lastLogs' => $lastLogLines
        ]);
    }
}