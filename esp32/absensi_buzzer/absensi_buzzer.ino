#include <Wire.h>
#include <LiquidCrystal_I2C.h>

const uint8_t BUZZER_PIN = 14;
const uint8_t RED_LED_PIN = 13;
const uint8_t GREEN_LED_PIN = 12;
const uint8_t LCD_ADDRESS = 0x27;
const uint8_t LCD_SDA_PIN = 22;
const uint8_t LCD_SCL_PIN = 23;
const uint32_t SERIAL_BAUD_RATE = 115200;
const uint16_t BEEP_ON_MS = 180;
const uint16_t BEEP_OFF_MS = 140;
const uint8_t UNKNOWN_BEEP_COUNT = 3;

LiquidCrystal_I2C lcd(LCD_ADDRESS, 16, 2);

bool buzzerActive = false;
bool buzzerOutputOn = false;
uint8_t beepsRemaining = 0;
uint32_t nextBuzzerChangeAt = 0;

String commandBuffer;

void stopBuzzer() {
  buzzerActive = false;
  buzzerOutputOn = false;
  beepsRemaining = 0;
  digitalWrite(BUZZER_PIN, LOW);
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

void startUnknownAlarm() {
  beepsRemaining = UNKNOWN_BEEP_COUNT;
  buzzerActive = true;
  buzzerOutputOn = true;
  digitalWrite(BUZZER_PIN, HIGH);
  nextBuzzerChangeAt = millis() + BEEP_ON_MS;
}

void startKnownBeep() {
  beepsRemaining = 1;
  buzzerActive = true;
  buzzerOutputOn = true;
  digitalWrite(BUZZER_PIN, HIGH);
  nextBuzzerChangeAt = millis() + 150; // Beep pendek sekali (150ms) tanda berhasil
}

void startFailureOutput(bool soundAlarm) {
  showAttendanceResult(false);
  if (soundAlarm) {
    startUnknownAlarm();
  } else {
    stopBuzzer();
  }
}

void processCommand(String command) {
  command.trim();
  command.toUpperCase();

  if (command == "UNKNOWN") {
    startFailureOutput(true);
    Serial.println("ACK:UNKNOWN");
  } else if (command == "FAIL") {
    startFailureOutput(false);
    Serial.println("ACK:FAIL");
  } else if (command == "TEST") {
    showOutputTest();
    startKnownBeep();
    Serial.println("ACK:TEST");
  } else if (command == "KNOWN" || command == "SUCCESS") {
    showAttendanceResult(true);
    startKnownBeep();
    Serial.println(command == "KNOWN" ? "ACK:KNOWN" : "ACK:SUCCESS");
  } else if (command == "OFF") {
    stopBuzzer();
    showStandby();
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
    digitalWrite(BUZZER_PIN, LOW);
    --beepsRemaining;
    if (beepsRemaining == 0) {
      buzzerActive = false;
      return;
    }
    nextBuzzerChangeAt = millis() + BEEP_OFF_MS;
    return;
  }

  buzzerOutputOn = true;
  digitalWrite(BUZZER_PIN, HIGH);
  nextBuzzerChangeAt = millis() + BEEP_ON_MS;
}

void setup() {
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(RED_LED_PIN, OUTPUT);
  pinMode(GREEN_LED_PIN, OUTPUT);
  stopBuzzer();
  digitalWrite(RED_LED_PIN, LOW);
  digitalWrite(GREEN_LED_PIN, LOW);
  Serial.begin(SERIAL_BAUD_RATE);
  Wire.begin(LCD_SDA_PIN, LCD_SCL_PIN);
  lcd.init();
  lcd.backlight();
  showStandby();
  commandBuffer.reserve(32);
  Serial.println("READY");
}

void loop() {
  readSerialCommands();
  updateBuzzer();
}
