<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Vue3Lottie } from 'vue3-lottie';
import DoctorAnimationData from '../../../public/assets/Doctor.json';
import axios from 'axios';
import { Howl } from 'howler';
import annyang from 'annyang';

// 1. تعريف "الإشارة" التي سيطلقها المكون للتواصل مع الصفحة الرئيسية
const emit = defineEmits(['perform-action']);

// --- State Variables ---
const lottieAnimation = ref(null);
const isListening = ref(false);
const transcript = ref('');
const botResponse = ref('');
let currentSound = null;

// --- Command Processing (Brain) ---
async function processCommand(command) {
  transcript.value = command;
  botResponse.value = '... أفكر في إجابة';
  if (currentSound) {
    currentSound.stop();
  }
  try {
    const response = await axios.post('/api/bot/ask', {
      question: command
    });
    
    const answerText = response.data.answer;
    const audioBase64 = response.data.audio;
    const action = response.data.action; // 2. استقبال الأمر من الخادم

    botResponse.value = answerText;
    
    if (audioBase64) {
      speakFromServer(audioBase64);
    }

    // 3. إذا كان هناك أمر، قم بإطلاق الإشارة إلى الصفحة الرئيسية
    if (action) {
      console.log("Emitting action to parent:", action);
      emit('perform-action', action);
    }

  } catch (error) {
    console.error('Error contacting the bot API:', error);
    botResponse.value = 'عذرًا، حدث خطأ أثناء الاتصال بالخادم.';
  }
}

// --- Speech Synthesis (Mouth) using Howler.js ---
function speakFromServer(audioBase64) {
  const audioSource = `data:audio/mp3;base64,${audioBase64}`;
  currentSound = new Howl({
    src: [audioSource],
    format: ['mp3']
  });
  currentSound.play();
}

// --- Speech Recognition (Ears) using annyang ---
onMounted(() => {
  if (annyang) {
    const commands = {
      '*text': (text) => {
        console.log("Annyang heard:", text);
        isListening.value = false;
        processCommand(text);
      }
    };
    annyang.addCommands(commands);
    annyang.setLanguage('ar-SA');
    annyang.addCallback('start', () => { isListening.value = true; transcript.value = '... أستمع الآن'; botResponse.value = ''; });
    annyang.addCallback('end', () => { if (isListening.value) { isListening.value = false; } });
    annyang.addCallback('error', (error) => { console.error('Annyang error:', error); isListening.value = false; });
  }
});

// Clean up annyang when the component is unmounted
onUnmounted(() => {
  if (annyang) {
    annyang.abort();
  }
});

// --- Control Function ---
function startListening() {
  if (annyang && !isListening.value) {
    annyang.start({ autoRestart: false, continuous: false });
  }
}
</script>

<template>
  <div class="ai-mascot-container">
    <transition name="dialog-fade">
      <div v-if="transcript || botResponse" class="dialog-box">
          <p v-if="transcript" class="text-right">{{ transcript }}</p>
          <p v-if="botResponse" class="response"><strong>البوت:</strong> {{ botResponse }}</p>
      </div>
    </transition>

    <div class="bot-area">
      <button @click="startListening" class="mic-button" :class="{ 'is-listening': isListening }">
        🎤
      </button>
      <div class="lottie-wrapper">
        <Vue3Lottie
          ref="lottieAnimation"
          :animationData="DoctorAnimationData"
          :loop="true"
          :autoPlay="true"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.ai-mascot-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.bot-area {
  display: flex;
  align-items: flex-end;
  gap: 10px;
}

.lottie-wrapper {
  width: 200px;
  height: 200px;
}

.mic-button {
  width: 60px;
  height: 60px;
  font-size: 30px;
  margin-bottom: 10px;
  border-radius: 50%;
  border: none;
  background-color: #0d9488;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.mic-button:hover {
  transform: scale(1.1);
}

.mic-button.is-listening {
  background-color: #ef4444;
  animation: pulse 1.5s infinite;
}

.dialog-box {
    background: white;
    padding: 10px 15px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    min-width: 280px;
    max-width: 350px;
    text-align: right;
    margin-bottom: 15px;
}
.dialog-box .response {
    color: #0d9488;
    margin-top: 8px;
    font-weight: 500;
}
p {
  margin: 0;
}

.dialog-fade-enter-active,
.dialog-fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}

.dialog-fade-enter-from,
.dialog-fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}


@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
  70% { box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
  100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
</style>