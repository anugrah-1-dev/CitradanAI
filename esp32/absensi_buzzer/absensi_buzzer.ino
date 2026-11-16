const uint8_t BUZZER_PIN = 23;
const uint32_t SERIAL_BAUD_RATE = 115200;
const uint16_t UNKNOWN_BEEP_ON_MS = 180;
const uint16_t UNKNOWN_BEEP_OFF_MS = 140;
const uint16_t KNOWN_BEEP_ON_MS = 180;
const uint16_t TEST_BEEP_ON_MS = 800;
const uint16_t PASSIVE_BUZZER_FREQUENCY = 2500;
const uint8_t UNKNOWN_BEEP_COUNT = 3;

bool buzzerActive = false;
bool buzzerOutputOn = false;
uint8_t beepsRemaining = 0;
uint16_t beepOnDuration = UNKNOWN_BEEP_ON_MS;
uint16_t beepOffDuration = UNKNOWN_BEEP_OFF_MS;
uint32_t nextBuzzerChangeAt = 0;

String scrollingName;
uint16_t nameScrollPosition = 0;
uint32_t nextNameScrollAt = 0;

uint32_t nextWifiRetryAt = 0;
bool serverStarted = false;

// Timer untuk mematikan LED merah otomatis
uint32_t redLedOffAt = 0;
bool redLedTimerActive = false;

// ---------- LCD ----------
void printLcdLine(uint8_t row, const String& text) {
  lcd.setCursor(0, row);
  for (uint8_t column = 0; column < LCD_COLUMNS; ++column) {
    lcd.print(column < text.length() ? text[column] : ' ');
  }
}

void showReadyScreen() {
  scrollingName = "";
  printLcdLine(0, "Sistem siap");
  printLcdLine(1, WiFi.status() == WL_CONNECTED ? "Scan wajah..." : "WiFi putus...");
}

void showUnknownScreen() {
  scrollingName = "";
  printLcdLine(0, "Wajah tidak");
  printLcdLine(1, "dikenali");
}

void showRecognizedScreen(const String& memberName) {
  scrollingName = memberName.length() > LCD_COLUMNS ? memberName + "   " : "";
  nameScrollPosition = 0;
  printLcdLine(0, "Member dikenali");
  printLcdLine(1, memberName);
  nextNameScrollAt = millis() + 450;
}

void updateScrollingName() {
  if (scrollingName.length() == 0 || static_cast<int32_t>(millis() - nextNameScrollAt) < 0) {
    return;
  }

  String visibleName;
  for (uint8_t offset = 0; offset < LCD_COLUMNS; ++offset) {
    visibleName += scrollingName[(nameScrollPosition + offset) % scrollingName.length()];
  }
  printLcdLine(1, visibleName);
  nameScrollPosition = (nameScrollPosition + 1) % scrollingName.length();
  nextNameScrollAt = millis() + 350;
}

// ---------- LED & Buzzer ----------
void setStatusLeds(bool knownFace) {
  const uint8_t ledOn = LED_ACTIVE_LOW ? LOW : HIGH;
  const uint8_t ledOff = LED_ACTIVE_LOW ? HIGH : LOW;
  digitalWrite(RED_LED_PIN, knownFace ? ledOff : ledOn);
  digitalWrite(GREEN_LED_PIN, knownFace ? ledOn : ledOff);

  if (knownFace) {
    redLedTimerActive = false;
  } else {
    redLedTimerActive = true;
    redLedOffAt = millis() + RED_LED_ON_DURATION_MS;
  }
}

void turnStatusLedsOff() {
  const uint8_t ledOff = LED_ACTIVE_LOW ? HIGH : LOW;
  digitalWrite(RED_LED_PIN, ledOff);
  digitalWrite(GREEN_LED_PIN, ledOff);
  redLedTimerActive = false;
}

void updateRedLed() {
  if (!redLedTimerActive || static_cast<int32_t>(millis() - redLedOffAt) < 0) {
    return;
  }
  digitalWrite(RED_LED_PIN, LED_ACTIVE_LOW ? HIGH : LOW);
  redLedTimerActive = false;
}

void buzzerOn() {
  if (BUZZER_IS_PASSIVE) {
    tone(BUZZER_PIN, PASSIVE_BUZZER_FREQUENCY);
  } else {
    digitalWrite(BUZZER_PIN, BUZZER_ACTIVE_LEVEL);
  }
}

void buzzerOff() {
  noTone(BUZZER_PIN);
  digitalWrite(BUZZER_PIN, BUZZER_ACTIVE_LEVEL == HIGH ? LOW : HIGH);
}

void stopBuzzer() {
  buzzerActive = false;
  buzzerOutputOn = false;
  beepsRemaining = 0;
  buzzerOff();
}

void showAttendanceResult(bool succeeded) {
  digitalWrite(RED_LED_PIN, succeeded ? LOW : HIGH);
  digitalWrite(GREEN_LED_PIN, succeeded ? HIGH : LOW);
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(succeeded ? "ABSEN BERHASIL" : "ABSEN GAGAL");
  lcd.setCursor(0, 1);
  lcd.print(succeeded ? "SILAKAN MASUK" : "SILAKAN CEK");
}

void showStandby() {
  digitalWrite(RED_LED_PIN, LOW);
  digitalWrite(GREEN_LED_PIN, LOW);
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("SISTEM ABSENSI");
  lcd.setCursor(0, 1);
  lcd.print("SIAP SCAN");
}

void showOutputTest() {
  digitalWrite(RED_LED_PIN, LOW);
  digitalWrite(GREEN_LED_PIN, HIGH);
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("TES OUTPUT OK");
  lcd.setCursor(0, 1);
  lcd.print("ESP32 SIAP");
}

void startBeepSequence(uint8_t count, uint16_t onDuration, uint16_t offDuration) {
  stopBuzzer();
  if (count == 0) {
    return;
  }

  beepsRemaining = count;
  beepOnDuration = onDuration;
  beepOffDuration = offDuration;
  buzzerActive = true;
  buzzerOutputOn = true;
  digitalWrite(BUZZER_PIN, HIGH);
  nextBuzzerChangeAt = millis() + 150; // Beep pendek sekali (150ms) tanda berhasil
}

void processCommand(String command) {
  command.trim();
  command.toUpperCase();

  if (command == "UNKNOWN") {
    startUnknownAlarm();
    Serial.println("ACK:UNKNOWN");
  } else if (command == "KNOWN" || command == "SUCCESS") {
    startKnownBeep();
    Serial.println("ACK:KNOWN");
  } else if (command == "OFF") {
    stopBuzzer();
    Serial.println("ACK:OFF");
  } else {
    Serial.println("ACK:INVALID");
  }
}

void readSerialCommands() {
  while (Serial.available() > 0) {
    const char incoming = static_cast<char>(Serial.read());
    if (incoming == '\n') {
      processCommand(commandBuffer);
      commandBuffer = "";
    } else if (incoming != '\r' && commandBuffer.length() < 32) {
      commandBuffer += incoming;
    } else if (commandBuffer.length() >= 32) {
      commandBuffer = "";
    }
  }
}

void updateBuzzer() {
  if (!buzzerActive || static_cast<int32_t>(millis() - nextBuzzerChangeAt) < 0) {
    return;
  }

  if (buzzerOutputOn) {
    buzzerOutputOn = false;
    buzzerOff();
    --beepsRemaining;
    if (beepsRemaining == 0) {
      buzzerActive = false;
      return;
    }
    nextBuzzerChangeAt = millis() + beepOffDuration;
    return;
  }

  buzzerOutputOn = true;
  buzzerOn();
  nextBuzzerChangeAt = millis() + beepOnDuration;
}

// ---------- Perintah ----------
void processCommand(String command, Stream& out) {
  command.trim();
  const int separator = command.indexOf(':');
  String action = separator >= 0 ? command.substring(0, separator) : command;
  String value = separator >= 0 ? command.substring(separator + 1) : "";
  action.trim();
  action.toUpperCase();
  value.trim();

  if (action == "UNKNOWN") {
    setStatusLeds(false);
    showUnknownScreen();
    startBeepSequence(UNKNOWN_BEEP_COUNT, UNKNOWN_BEEP_ON_MS, UNKNOWN_BEEP_OFF_MS);
    out.println("ACK:UNKNOWN");
  } else if (action == "KNOWN" || action == "SUCCESS") {
    setStatusLeds(true);
    startBeepSequence(1, KNOWN_BEEP_ON_MS, 0);
    showRecognizedScreen(value.length() > 0 ? value : "Member dikenali");
    out.println("ACK:KNOWN");
  } else if (action == "TEST") {
    setStatusLeds(true);
    scrollingName = "";
    printLcdLine(0, "Tes perangkat");
    printLcdLine(1, "ESP32 siap");
    startBeepSequence(1, TEST_BEEP_ON_MS, 0);
    out.println("ACK:TEST");
  } else if (action == "OFF") {
    stopBuzzer();
    turnStatusLedsOff();
    showReadyScreen();
    out.println("ACK:OFF");
  } else if (action == "PING") {
    out.println("PONG");
  } else if (action == "IP") {
    out.println(WiFi.localIP().toString());
  } else {
    out.println("ACK:INVALID");
  }
}

void readCommands(Stream& in, LineReader& reader) {
  while (in.available() > 0) {
    const char incoming = static_cast<char>(in.read());
    if (incoming == '\n') {
      if (!reader.overflow) {
        processCommand(reader.buffer, in);
      } else {
        in.println("ACK:INVALID");
      }
      reader.buffer = "";
      reader.overflow = false;
    } else if (incoming != '\r' && !reader.overflow) {
      if (reader.buffer.length() < MAX_COMMAND_LENGTH) {
        reader.buffer += incoming;
      } else {
        reader.buffer = "";
        reader.overflow = true;
      }
    }
  }
}

// ---------- HTTP (untuk web) ----------
void sendCorsHeaders() {
  httpServer.sendHeader("Access-Control-Allow-Origin", "*");
  httpServer.sendHeader("Access-Control-Allow-Methods", "GET, OPTIONS");
  httpServer.sendHeader("Access-Control-Allow-Headers", "*");
  httpServer.sendHeader("Access-Control-Allow-Private-Network", "true");
}

void handleCommandHttp() {
  sendCorsHeaders();
  if (httpServer.method() == HTTP_OPTIONS) {
    httpServer.send(204);
    return;
  }
  if (!httpServer.hasArg("c")) {
    httpServer.send(400, "text/plain", "ACK:INVALID");
    return;
  }
  StringStream out;
  processCommand(httpServer.arg("c"), out);
  out.data.trim();
  httpServer.send(200, "text/plain", out.data);
}

// ---------- WiFi ----------
void startNetworkServices() {
  if (serverStarted) {
    return;
  }
  if (MDNS.begin(MDNS_HOSTNAME)) {
    MDNS.addService("http", "tcp", 80);
    MDNS.addService("faceaccess", "tcp", TCP_PORT);
  }
  tcpServer.begin();
  tcpServer.setNoDelay(true);

  httpServer.on("/cmd", HTTP_ANY, handleCommandHttp);
  httpServer.begin();

  serverStarted = true;
}

void connectWifiAtBoot() {
  WiFi.mode(WIFI_STA);
  WiFi.setAutoReconnect(true);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  printLcdLine(0, "Menyambung WiFi");
  printLcdLine(1, WIFI_SSID);

  const uint32_t startedAt = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startedAt < WIFI_CONNECT_TIMEOUT_MS) {
    delay(250);
  }

  if (WiFi.status() == WL_CONNECTED) {
    startNetworkServices();
    printLcdLine(0, "WiFi terhubung");
    printLcdLine(1, WiFi.localIP().toString());
    Serial.print("WIFI:");
    Serial.println(WiFi.localIP());
    delay(2500);
  } else {
    printLcdLine(0, "WiFi gagal");
    printLcdLine(1, "Mode USB saja");
    Serial.println("WIFI:FAILED");
    delay(2000);
  }
}

void maintainWifi() {
  if (WiFi.status() == WL_CONNECTED) {
    startNetworkServices();
    return;
  }
  if (static_cast<int32_t>(millis() - nextWifiRetryAt) >= 0) {
    WiFi.disconnect();
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
    nextWifiRetryAt = millis() + 10000;
  }
}

void handleTcpClient() {
  if (!serverStarted) {
    return;
  }

  if (!tcpClient || !tcpClient.connected()) {
    if (tcpClient) {
      tcpClient.stop();
      wifiReader.buffer = "";
      wifiReader.overflow = false;
    }
    WiFiClient incoming = tcpServer.available();
    if (incoming) {
      tcpClient = incoming;
      tcpClient.setNoDelay(true);
      tcpClient.println("READY");
    }
    return;
  }

  readCommands(tcpClient, wifiReader);
}

// ---------- Arduino ----------
void setup() {
  // Tulis level OFF dulu sebelum pinMode agar LED tidak menyala sekilas saat boot
  digitalWrite(RED_LED_PIN, LED_ACTIVE_LOW ? HIGH : LOW);
  digitalWrite(GREEN_LED_PIN, LED_ACTIVE_LOW ? HIGH : LOW);

  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(RED_LED_PIN, OUTPUT);
  pinMode(GREEN_LED_PIN, OUTPUT);
  stopBuzzer();
  Serial.begin(SERIAL_BAUD_RATE);
  commandBuffer.reserve(32);
  Serial.println("READY");
}

void loop() {
  maintainWifi();
  readCommands(Serial, serialReader);
  handleTcpClient();
  httpServer.handleClient();
  updateBuzzer();
  updateScrollingName();
  updateRedLed();
}