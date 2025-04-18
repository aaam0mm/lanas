import os
import undetected_chromedriver as uc
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options

# current_dir = os.path.dirname(os.path.abspath(__file__))
CHROME_DRIVER_PATH = '/Applications/MAMP/htdocs/nas/exe/chromedriver'

def start_driver(headless=True, block_ads=False, captcha=False, display_images=True, page_load_timeout=600, port=4444):
    chrome_options = Options()

    if headless:
        chrome_options.add_argument("--headless")
        chrome_options.add_argument("--disable-gpu")
        chrome_options.add_argument("--disable-blink-features=AutomationControlled")
        chrome_options.add_argument("--no-sandbox")
        chrome_options.add_argument("--disable-dev-shm-usage")
        chrome_options.add_argument("--window-size=800x600")
        chrome_options.add_argument("--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36")

    if not display_images:
        chrome_options.add_argument("--blink-settings=imagesEnabled=false")

    if block_ads:
        chrome_options.add_extension('/Applications/MAMP/htdocs/nas/crxs/AdBlock.crx')

    if captcha:
        chrome_options.add_extension('/Applications/MAMP/htdocs/nas/crxs/reCAPTCHA.crx')

    service = Service(CHROME_DRIVER_PATH, port=port)
    driver = uc.Chrome(service=service, options=chrome_options, remote_options={'port': port})

    driver.set_page_load_timeout(page_load_timeout)

    # Print the command executor URL
    print(driver.command_executor._url)

    return driver

if __name__ == "__main__":
    driver = start_driver(headless=False, block_ads=False, captcha=True, display_images=False, page_load_timeout=600)
    try:
        # Keep the script running
        while True:
            pass
    except KeyboardInterrupt:
        print("Stopping the driver.")
        driver.quit()
