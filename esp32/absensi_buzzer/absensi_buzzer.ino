const uint8_t BUZZER_PIN = 23;
const uint32_t SERIAL_BAUD_RATE = 115200;
const uint16_t BEEP_ON_MS = 180;
const uint16_t BEEP_OFF_MS = 140;
const uint8_t UNKNOWN_BEEP_COUNT = 3;

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

void startUnknownAlarm() {
  beepsRemaining = UNKNOWN_BEEP_COUNT;
  buzzerActive = true;
  buzzerOutputOn = true;
  digitalWrite(BUZZER_PIN, HIGH);
  nextBuzzerChangeAt = millis() + BEEP_ON_MS;
}

void processCommand(String command) {
  command.trim();
  command.toUpperCase();

  if (command == "UNKNOWN") {
    startUnknownAlarm();
  } else if (command == "OFF" || command == "KNOWN") {
    stopBuzzer();
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
  stopBuzzer();
  Serial.begin(SERIAL_BAUD_RATE);
  commandBuffer.reserve(32);
}

void loop() {
  readSerialCommands();
  updateBuzzer();
}
