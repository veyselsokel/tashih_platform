from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.common.exceptions import TimeoutException
from webdriver_manager.chrome import ChromeDriverManager
import time
import smtplib
from email.mime.text import MIMEText
from datetime import datetime
import json
import argparse
import sys
import logging
import os


# DEBUG bilgilerini yazdır
print("========== BAŞLATILIYOR ==========")
print(f"Çalışma dizini: {os.getcwd()}")
print(f"Script konumu: {os.path.dirname(os.path.abspath(__file__))}")
print("=================================")

# Argüman parser'ı ayarlama
parser = argparse.ArgumentParser(description="Fiyat Takip Uygulaması")
parser.add_argument("--config", help="Konfigürasyon JSON dosyası yolu")
parser.add_argument("--log", help="Log dosyası yolu")
parser.add_argument("--base-dir", help="Laravel projesinin kök dizini")
args = parser.parse_args()

# DEBUG: Argümanları yazdır
print(f"Alınan argümanlar: {args}")

# Base directory'i kullan
if args.base_dir:
    try:
        print(f"Çalışma dizini değiştiriliyor: {args.base_dir}")
        os.chdir(args.base_dir)
        print(f"Çalışma dizini değiştirildi. Yeni dizin: {os.getcwd()}")
    except Exception as e:
        print(f"HATA: Çalışma dizini değiştirilemedi: {e}")

# Log ayarları
if args.log:
    log_path = os.path.abspath(args.log)
    print(f"Log dosyası yolu: {log_path}")
    log_dir = os.path.dirname(log_path)

    # Log dizini yoksa oluştur
    if not os.path.exists(log_dir):
        try:
            os.makedirs(log_dir)
            print(f"Log dizini oluşturuldu: {log_dir}")
        except Exception as e:
            print(f"Log dizini oluşturulamadı: {e}")

    logging.basicConfig(
        filename=log_path,
        level=logging.INFO,
        format="%(asctime)s - %(levelname)s - %(message)s",
        datefmt="%Y-%m-%d %H:%M:%S",
    )
    # Hem konsola hem de dosyaya yazmak için
    console = logging.StreamHandler()
    console.setLevel(logging.INFO)
    logging.getLogger("").addHandler(console)
else:
    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s - %(levelname)s - %(message)s",
        datefmt="%Y-%m-%d %H:%M:%S",
    )

logger = logging.getLogger("price_tracker")

# Config dosyası kontrolü
if args.config:
    # Mutlak yola dönüştür
    config_path = os.path.abspath(args.config)
    logger.info(f"Konfigürasyon dosyası okunuyor (mutlak yol): {config_path}")
    print(f"Konfigürasyon dosyası mutlak yolu: {config_path}")
    print(f"Dosya var mı: {os.path.exists(config_path)}")

    if not os.path.exists(config_path):
        logger.error(f"Konfigürasyon dosyası bulunamadı: {config_path}")
        logger.error(f"Mevcut çalışma dizini: {os.getcwd()}")

        # Dizin içeriğini listele
        parent_dir = os.path.dirname(config_path)
        if os.path.exists(parent_dir):
            logger.error(f"Dizin içeriği ({parent_dir}):")
            for item in os.listdir(parent_dir):
                logger.error(f" - {item}")

        # Son bir deneme daha yap - göreli yoldan
        rel_config_path = args.config
        logger.info(f"Göreli yoldan deneniyor: {rel_config_path}")
        print(f"Göreli yoldan deneniyor: {rel_config_path}")
        print(f"Göreli yol var mı: {os.path.exists(rel_config_path)}")

        if os.path.exists(rel_config_path):
            logger.info(f"Göreli yoldan bulundu: {rel_config_path}")
            config_path = rel_config_path
        else:
            logger.error("Hem mutlak hem de göreli yoldan bulunamadı!")
            sys.exit(1)
    else:
        logger.info(f"Konfigürasyon dosyası bulundu: {config_path}")
else:
    logger.error("HATA: Konfigürasyon dosya yolu belirtilmedi")
    sys.exit(1)

# Konfigürasyonu oku
try:
    with open(config_path, "r") as f:
        config = json.load(f)

    email = config.get("email")
    app_password = config.get("app_password")
    products = config.get("products", [])
    check_interval = config.get("check_interval", 300)

    logger.info(f"Konfigürasyon başarıyla yüklendi: {len(products)} ürün bulundu")
    print(f"Konfigürasyon başarıyla yüklendi: {len(products)} ürün bulundu")
except Exception as e:
    logger.error(f"Konfigürasyon dosyası okuma hatası: {e}")
    print(f"HATA: Konfigürasyon dosyası okunamadı: {e}")
    # Dosya içeriğini göster
    try:
        with open(config_path, "r") as f:
            content = f.read()
        print(f"Dosya içeriği ({len(content)} karakter): {content[:200]}...")
    except Exception as e2:
        print(f"Dosya içeriği okunamadı: {e2}")
    sys.exit(1)


class PriceMonitor:
    def __init__(self):
        self.products = []
        self.email_sender = None
        self.email_password = None
        self.setup_driver()
        self.stores = {1: "zara", 2: "pull&bear"}

    def parse_price(self, price_text):
        """Fiyat metnini sayıya çevirir"""
        try:
            # TL ve boşlukları kaldır
            price_text = price_text.replace("TL", "").strip()
            # Binlik ayracı olan virgülü kaldır
            price_text = price_text.replace(",", "")
            # Sayıya çevir
            return float(price_text)
        except Exception as e:
            logger.error(f"Fiyat ayrıştırma hatası: {e}")
            return None

    def setup_driver(self):
        """Selenium driver'ı ayarlar"""
        logger.info("Chrome Driver ayarlanıyor...")
        chrome_options = Options()
        chrome_options.add_argument("--headless")
        chrome_options.add_argument("--no-sandbox")
        chrome_options.add_argument("--disable-dev-shm-usage")
        chrome_options.add_argument("--disable-blink-features=AutomationControlled")
        chrome_options.add_argument("--window-size=1920,1080")
        chrome_options.add_argument(
            "user-agent=Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36"
        )
        chrome_options.add_experimental_option("excludeSwitches", ["enable-automation"])
        chrome_options.add_experimental_option("useAutomationExtension", False)

        try:
            self.driver = webdriver.Chrome(
                service=Service(ChromeDriverManager().install()), options=chrome_options
            )
            self.driver.execute_script(
                "Object.defineProperty(navigator, 'webdriver', {get: () => undefined})"
            )
            self.wait = WebDriverWait(self.driver, 20)
            logger.info("Chrome Driver başarıyla ayarlandı")
        except Exception as e:
            logger.error(f"Chrome Driver ayarlanırken hata oluştu: {e}")
            raise

    def add_product(self, url, target_price, store="zara"):
        self.products.append(
            {
                "url": url,
                "target_price": float(target_price),
                "store": store.lower(),
                "last_price": None,
                "last_check": None,
            }
        )
        logger.info(
            f"Ürün eklendi: {url}, Hedef Fiyat: {target_price} TL, Mağaza: {store}"
        )

    def check_zara_price(self, url):
        try:
            logger.info(f"Zara sayfası yükleniyor: {url}")
            self.driver.get(url)
            time.sleep(10)

            logger.info("Fiyat elementleri aranıyor...")
            current_price_text = None
            old_price_text = None
            current_price = None
            old_price = None

            # İndirimli fiyatı kontrol et
            try:
                current_price_element = self.wait.until(
                    EC.presence_of_element_located(
                        (
                            By.CSS_SELECTOR,
                            'span[data-qa-qualifier="price-amount-current"] .money-amount__main',
                        )
                    )
                )
                current_price_text = current_price_element.text
                logger.info(f"İndirimli fiyat bulundu: {current_price_text}")
                current_price = float(
                    current_price_text.replace("TL", "")
                    .replace(".", "")
                    .replace(",", ".")
                    .strip()
                )
            except Exception as e:
                logger.warning(f"İndirimli fiyat bulunamadı: {e}")

            # Normal fiyatı kontrol et
            try:
                old_price_element = self.wait.until(
                    EC.presence_of_element_located(
                        (
                            By.CSS_SELECTOR,
                            'span[data-qa-qualifier="price-amount-old"] .money-amount__main',
                        )
                    )
                )
                old_price_text = old_price_element.text
                logger.info(f"Normal fiyat bulundu: {old_price_text}")
                old_price = float(
                    old_price_text.replace("TL", "")
                    .replace(".", "")
                    .replace(",", ".")
                    .strip()
                )
            except Exception as e:
                logger.warning(f"Normal fiyat bulunamadı: {e}")

            # İndirimli fiyat varsa onu, yoksa normal fiyatı kullan
            if current_price is not None:
                return {
                    "current_price": current_price,
                    "old_price": old_price,
                    "current_price_text": current_price_text,
                    "old_price_text": old_price_text,
                }
            elif old_price is not None:
                return {
                    "current_price": old_price,
                    "old_price": None,
                    "current_price_text": old_price_text,
                    "old_price_text": None,
                }
            else:
                logger.warning("Hiçbir fiyat bulunamadı!")
                return None

        except Exception as e:
            logger.error(f"Zara fiyat kontrolünde genel hata: {e}")
            return None

    def check_pull_and_bear_price(self, url):
        try:
            logger.info(f"Pull&Bear sayfası yükleniyor: {url}")
            self.driver.get(url)
            time.sleep(10)  # Sayfanın yüklenmesi için bekle

            # Page source'u al ve analiz et
            logger.info("Sayfa yüklendi, fiyat aranıyor...")

            # Daha genel bir seçici deneyelim
            price_elements = self.driver.find_elements(
                By.CSS_SELECTOR, ".price-current-price"
            )

            if price_elements:
                price_text = price_elements[0].text
                logger.info(f"Fiyat bulundu: {price_text}")
                # Temizle ve dönüştür
                price = float(
                    price_text.replace("TL", "")
                    .replace(".", "")
                    .replace(",", ".")
                    .replace("\xa0", "")
                    .strip()
                )
                return price

            # Alternatif seçiciler
            price_elements = self.driver.find_elements(By.CSS_SELECTOR, ".price span")
            if price_elements:
                price_text = price_elements[0].text
                logger.info(f"Alternatif fiyat bulundu: {price_text}")
                price = float(
                    price_text.replace("TL", "")
                    .replace(".", "")
                    .replace(",", ".")
                    .replace("\xa0", "")
                    .strip()
                )
                return price

            logger.warning(
                "Hiçbir fiyat seçici eşleşmedi, sayfa kaynağını kontrol edelim..."
            )

            # İkinci bir yaklaşım olarak JavaScript kullanarak deneyebilirsiniz
            price_js = self.driver.execute_script(
                """
                return document.querySelector('.price-current-price') ? 
                    document.querySelector('.price-current-price').textContent : null;
            """
            )

            if price_js:
                logger.info(f"JavaScript ile fiyat bulundu: {price_js}")
                price = float(
                    price_js.replace("TL", "")
                    .replace(".", "")
                    .replace(",", ".")
                    .replace("\xa0", "")
                    .strip()
                )
                return price

            logger.warning("Hiçbir fiyat bulunamadı!")
            return None

        except Exception as e:
            logger.error(f"Pull&Bear fiyat kontrolünde genel hata: {e}")
            return None

    def send_notification(
        self, product, current_price, old_price=None, is_price_drop=False
    ):
        try:
            logger.info("Email bildirimi hazırlanıyor...")

            # Email içeriği oluştur
            if is_price_drop:
                subject = "Fiyat Düştü! - İndirim Alarmı"
                content_lines = [
                    f"Ürün: {product['url']}",
                    f"Mevcut Fiyat: {current_price} TL",
                ]

                # Eski fiyat bilgisi varsa ekle
                if old_price:
                    content_lines.append(f"Önceki Fiyat: {old_price} TL")
                    discount_percent = ((old_price - current_price) / old_price) * 100
                    content_lines.append(f"İndirim Oranı: %{discount_percent:.2f}")

                content_lines.extend(
                    [
                        f"Hedef Fiyat: {product['target_price']} TL",
                        f"Kontrol Zamanı: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}",
                    ]
                )
            else:
                subject = "Hedef Fiyata Ulaşıldı! - Fiyat Uyarısı"
                content_lines = [
                    f"Ürün: {product['url']}",
                    f"Güncel Fiyat: {current_price} TL (Hedef fiyata ulaşıldı)",
                    f"Hedef Fiyat: {product['target_price']} TL",
                    f"Kontrol Zamanı: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}",
                ]

            email_content = "\n".join(content_lines)
            logger.info(f"Email içeriği:\n{email_content}")

            msg = MIMEText(email_content)
            msg["Subject"] = subject
            msg["From"] = self.email_sender
            msg["To"] = self.email_sender

            logger.info("SMTP sunucusuna bağlanılıyor...")
            try:
                with smtplib.SMTP_SSL("smtp.gmail.com", 465) as smtp:
                    logger.info("SMTP sunucusuna bağlantı başarılı")
                    logger.info("Giriş yapılıyor...")
                    smtp.login(self.email_sender, self.email_password)
                    logger.info("Giriş başarılı")
                    logger.info("Email gönderiliyor...")
                    smtp.send_message(msg)
                    logger.info("Email başarıyla gönderildi")
            except smtplib.SMTPAuthenticationError:
                logger.error(
                    "SMTP kimlik doğrulama hatası! Lütfen email ve app password'ü kontrol edin."
                )
            except smtplib.SMTPException as e:
                logger.error(f"SMTP hatası: {e}")

        except Exception as e:
            logger.error(f"Email gönderiminde genel hata: {e}")
            logger.error(f"Hata detayı: {str(e.__class__.__name__)}")

    def monitor_prices(self, check_interval=300):
        logger.info("\nFiyat takibi başlatılıyor...")
        try:
            # İlk çalıştırmada mevcut indirimler için bildirim yapılıp yapılmayacağı
            first_run = True

            while True:
                for product in self.products:
                    logger.info(f"\nÜrün kontrol ediliyor: {product['url']}")
                    price_info = None
                    old_price = None

                    if product["store"] == "zara":
                        price_info = self.check_zara_price(product["url"])
                        if price_info:
                            current_price = price_info["current_price"]
                            old_price = price_info[
                                "old_price"
                            ]  # Eski fiyat bilgisini al

                            logger.info(
                                f"Mevcut fiyat: {current_price}, Eski liste fiyatı: {old_price}"
                            )
                        else:
                            current_price = None
                    elif product["store"] == "pull&bear":
                        current_price = self.check_pull_and_bear_price(product["url"])
                        logger.info(f"Pull&Bear fiyat: {current_price}")
                    else:
                        logger.warning(f"Desteklenmeyen mağaza: {product['store']}")
                        current_price = None

                    if current_price:
                        # İlk kontrol ise, son fiyat olarak kaydet
                        if product["last_price"] is None:
                            product["last_price"] = current_price
                            product["last_check"] = datetime.now()
                            logger.info(
                                f"İlk kontrol: Fiyat {current_price} TL olarak kaydedildi."
                            )

                            # İlk çalıştırmada mevcut indirimler için bildirim gönder
                            if first_run and old_price and old_price > current_price:
                                logger.info(
                                    f"Mevcut indirim tespit edildi! Orijinal fiyat: {old_price} TL, İndirimli fiyat: {current_price} TL"
                                )
                                self.send_notification(
                                    product,
                                    current_price,
                                    old_price,
                                    is_price_drop=True,
                                )
                            continue

                        logger.info(
                            f"Karşılaştırma: Son fiyat = {product['last_price']}, Şimdiki fiyat = {current_price}, Hedef fiyat = {product['target_price']}"
                        )

                        # Fiyat düşüşü varsa bildirim gönder
                        if current_price < product["last_price"]:
                            logger.info(
                                f"Fiyat düşüşü tespit edildi! {product['last_price']} TL -> {current_price} TL"
                            )
                            self.send_notification(
                                product,
                                current_price,
                                product["last_price"],
                                is_price_drop=True,
                            )
                        # Hedef fiyatı yakaladıysa ve ilk defa hedef fiyata ulaşıyorsa bildirim gönder
                        elif (
                            current_price <= product["target_price"]
                            and product["last_price"] > product["target_price"]
                        ):
                            logger.info(
                                f"Hedef fiyata ulaşıldı! Hedef: {product['target_price']} TL, Güncel: {current_price} TL"
                            )
                            self.send_notification(
                                product, current_price, old_price, is_price_drop=False
                            )

                        # Son fiyat ve kontrol zamanını güncelle
                        product["last_price"] = current_price
                        product["last_check"] = datetime.now()
                    else:
                        logger.warning("Fiyat alınamadı!")

                # İlk tur tamamlandı
                first_run = False
                logger.info(f"\n{check_interval} saniye bekleniyor...")
                time.sleep(check_interval)

        except KeyboardInterrupt:
            logger.info("\nProgram durduruluyor...")
        finally:
            self.driver.quit()


def main():
    try:
        # Konfigürasyon dosyasından verileri al
        logger.info("Ana program başlatılıyor...")

        if not products:
            logger.warning("\nHiç ürün eklenmedi. Program sonlandırılıyor...")
            return

        # Price Monitor'ı başlat
        monitor = PriceMonitor()

        # Email ayarlarını güncelle
        monitor.email_sender = email
        monitor.email_password = app_password

        # Ürünleri ekle
        for product in products:
            # API'den gelen ürünler ile CLI'dan gelen ürünlerin yapıları farklı olabilir
            if isinstance(product, dict):
                if (
                    "url" in product
                    and ("target_price" in product or "targetPrice" in product)
                    and "store" in product
                ):
                    target_price = product.get(
                        "target_price", product.get("targetPrice")
                    )
                    monitor.add_product(
                        url=product["url"],
                        target_price=target_price,
                        store=product["store"],
                    )
                else:
                    logger.warning(f"Geçersiz ürün formatı: {product}")
            else:
                logger.warning(f"Geçersiz ürün tipi: {type(product)}")

        logger.info("\n=== Program Başlatılıyor ===")
        logger.info(f"Takip edilen ürün sayısı: {len(monitor.products)}")
        logger.info(f"Kontrol aralığı: {check_interval} saniye")
        logger.info("----------------------------")

        # Fiyat takibini başlat
        monitor.monitor_prices(check_interval=check_interval)

    except KeyboardInterrupt:
        logger.info("\nProgram kullanıcı tarafından durduruldu.")
    except Exception as e:
        logger.error(f"\nBeklenmeyen bir hata oluştu: {e}")
        import traceback

        logger.error(traceback.format_exc())
        sys.exit(1)


if __name__ == "__main__":
    main()
