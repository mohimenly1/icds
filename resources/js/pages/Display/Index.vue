<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { gsap } from 'gsap';

const props = defineProps({
    screen: Object,
    media: Array,
});

const image1 = ref(null);
const image2 = ref(null);
const videoPlayer = ref(null);
const currentMediaElement = ref(null);

let currentIndex = 0;
let timeline;

const applyKenBurns = (element) => {
  const scale = 1.2 + Math.random() * 0.2;
  const x = (Math.random() - 0.5) * 20;
  const y = (Math.random() - 0.5) * 20;
  gsap.fromTo(element, 
    { scale: 1, x: '0%', y: '0%' }, 
    { scale: scale, x: `${x}%`, y: `${y}%`, duration: 7, ease: 'linear' }
  );
};

const playNext = () => {
    if (!props.media || props.media.length === 0) return;

    const mediaItem = props.media[currentIndex];
    
    if (mediaItem.type === 'image') {
        const nextImageEl = currentMediaElement.value === image1.value ? image2.value : image1.value;
        
        nextImageEl.src = mediaItem.path;
        applyKenBurns(nextImageEl);
        
        gsap.to(currentMediaElement.value, { autoAlpha: 0, duration: 1.5 });
        gsap.to(nextImageEl, { autoAlpha: 1, duration: 1.5 });
        
        currentMediaElement.value = nextImageEl;
        
        timeline = gsap.delayedCall(7, playNext);
    } else { // Video
        gsap.to(currentMediaElement.value, { autoAlpha: 0, duration: 1.5 });
        videoPlayer.value.src = mediaItem.path;
        gsap.to(videoPlayer.value, { autoAlpha: 1, duration: 1.5, onComplete: () => videoPlayer.value.play() });
    }

    currentIndex = (currentIndex + 1) % props.media.length;
};

const handleVideoEnd = () => {
    gsap.to(videoPlayer.value, { autoAlpha: 0, duration: 1.5, onComplete: playNext });
};

onMounted(() => {
    if (props.media && props.media.length > 0) {
        currentMediaElement.value = image1.value;
        playNext();
    }
});

onUnmounted(() => {
    if (timeline) timeline.kill();
});
</script>

<template>
    <Head :title="screen.name" />
    <div class="w-screen h-screen bg-black overflow-hidden">
        <img ref="image1" class="absolute w-full h-full object-cover opacity-0" />
        <img ref="image2" class="absolute w-full h-full object-cover opacity-0" />
        <video ref="videoPlayer" @ended="handleVideoEnd" class="absolute w-full h-full object-cover opacity-0" muted playsinline></video>
    </div>
</template>
