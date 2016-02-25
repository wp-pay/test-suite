// http://www.chrisle.me/2013/08/running-headless-selenium-with-chrome/

var webdriver = require('selenium-webdriver');

var keyword = "pronamic";

var driver = new webdriver.Builder().
   usingServer('http://localhost:4444/wd/hub').
   withCapabilities(webdriver.Capabilities.chrome()).
   build();

driver.get('http://www.google.com');
driver.findElement(webdriver.By.name('q')).sendKeys(keyword);
driver.findElement(webdriver.By.name('btnG')).click();
driver.wait(function() {
  return driver.getTitle().then(function(title) {
    driver.getPageSource().then(function(html) {
      console.log(html);
      return true;
    });
  });
}, 1000);

driver.quit();
