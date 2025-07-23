<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import * as THREE from 'three';
import { gsap } from 'gsap';
import { EffectComposer } from 'three/examples/jsm/postprocessing/EffectComposer.js';
import { RenderPass } from 'three/examples/jsm/postprocessing/RenderPass.js';
import { UnrealBloomPass } from 'three/examples/jsm/postprocessing/UnrealBloomPass.js';
import { ShaderPass } from 'three/examples/jsm/postprocessing/ShaderPass.js';
import { VignetteShader } from 'three/examples/jsm/shaders/VignetteShader.js';

const sceneContainer = ref(null);
const mainContainer = ref(null);

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        mainContainer.value?.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
};

onMounted(() => {
    if (!sceneContainer.value) return;

    let animationFrameId;
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, sceneContainer.value.clientWidth / sceneContainer.value.clientHeight, 0.1, 1000);
    camera.position.z = 5;

    const renderer = new THREE.WebGLRenderer({ antialias: true, logarithmicDepthBuffer: true }); //
    renderer.setSize(sceneContainer.value.clientWidth, sceneContainer.value.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    sceneContainer.value.appendChild(renderer.domElement);

    const ambientLight = new THREE.AmbientLight(0xffffff, 1.5);
    scene.add(ambientLight);
    const directionalLight = new THREE.DirectionalLight(0xffffff, 2);
    directionalLight.position.set(-1, 1, 3);
    scene.add(directionalLight);
    
    // --- العنصر الدائري المتوهج ---
    let glowingRing;
    const ringGeometry = new THREE.RingGeometry(0.5, 0.55, 64);
    
    // =================================================================
    //  ✅ التعديل هنا: نضمن أن المادة سترسم دائمًا في المقدمة
    // =================================================================
    const ringMaterial = new THREE.MeshBasicMaterial({ 
        color: 0x00abff, 
        side: THREE.DoubleSide,
        depthTest: false // تعطيل اختبار العمق لرسمها دائمًا فوق العناصر الأخرى
    });
    // =================================================================

    glowingRing = new THREE.Mesh(ringGeometry, ringMaterial);
    scene.add(glowingRing);


    // --- عرض الصور ---
    const textureLoader = new THREE.TextureLoader();
    const imagePaths = [
        './assets/1.jpg', './assets/2.jpg', './assets/3.jpg', 
        './assets/4.jpg', './assets/5.jpg', './assets/6.jpg', './assets/7.jpg',
    ];

    let imagePlanes = [];
    let currentImageIndex = 0;
    
    const getPlaneSize = () => {
        const distance = camera.position.z;
        const vFov = THREE.MathUtils.degToRad(camera.fov);
        const height = 2 * Math.tan(vFov / 2) * distance;
        const width = height * camera.aspect;
        return { width, height };
    };

    const { width, height } = getPlaneSize();
    const geometry = new THREE.PlaneGeometry(width, height);

    imagePaths.forEach((path, index) => {
        const texture = textureLoader.load(path);
        const material = new THREE.MeshStandardMaterial({ map: texture, transparent: true, opacity: index === 0 ? 1 : 0 });
        const plane = new THREE.Mesh(geometry, material);
        // ندفع الصور قليلاً إلى الخلف
        plane.position.z = -0.1;
        plane.visible = index === 0;
        imagePlanes.push(plane);
        scene.add(plane);
    });

    let kenBurnsTimeline;
    function applyKenBurnsEffect(plane) {
        if (kenBurnsTimeline) kenBurnsTimeline.kill();
        plane.scale.set(1, 1, 1);
        kenBurnsTimeline = gsap.timeline().to(plane.scale, { duration: 7, x: 1.15, y: 1.15, ease: 'power1.inOut' });
    }

    function switchImage() {
        const previousImageIndex = currentImageIndex;
        currentImageIndex = (currentImageIndex + 1) % imagePlanes.length;

        const previousPlane = imagePlanes[previousImageIndex];
        const currentPlane = imagePlanes[currentImageIndex];
        
        currentPlane.visible = true;
        currentPlane.material.opacity = 0;
        
        gsap.to(previousPlane.material, { duration: 2.0, opacity: 0, ease: 'power2.inOut', onComplete: () => { previousPlane.visible = false; }});
        gsap.to(currentPlane.material, { duration: 2.5, opacity: 1, ease: 'power2.inOut' });
        
        applyKenBurnsEffect(currentPlane);
    }

    applyKenBurnsEffect(imagePlanes[0]);
    setInterval(switchImage, 6000);

    const composer = new EffectComposer(renderer);
    composer.addPass(new RenderPass(scene, camera));

    const bloomPass = new UnrealBloomPass(new THREE.Vector2(window.innerWidth / 2, window.innerHeight), 0.25, 0.5, 0.8);
    composer.addPass(bloomPass);

    const vignettePass = new ShaderPass(VignetteShader);
    vignettePass.uniforms['offset'].value = 0.95;
    vignettePass.uniforms['darkness'].value = 0.9;
    composer.addPass(vignettePass);

    const clock = new THREE.Clock();
    function animate() {
        animationFrameId = requestAnimationFrame(animate);
        if (glowingRing) {
            glowingRing.rotation.z -= 0.005;
        }
        composer.render();
    }
    
    const handleResize = () => {
        const newWidth = sceneContainer.value.clientWidth;
        const newHeight = sceneContainer.value.clientHeight;

        camera.aspect = newWidth / newHeight;
        camera.updateProjectionMatrix();

        renderer.setSize(newWidth, newHeight);
        composer.setSize(newWidth, newHeight);
        
        const { width: planeWidth, height: planeHeight } = getPlaneSize();

        imagePlanes.forEach(plane => {
            plane.geometry.dispose();
            plane.geometry = new THREE.PlaneGeometry(planeWidth, planeHeight);
        });

        if (glowingRing) {
            const margin = 0.8;
            glowingRing.position.x = planeWidth / 2 - margin;
            glowingRing.position.y = -planeHeight / 2 + margin;
        }
    };

    handleResize();
    animate();

    window.addEventListener('resize', handleResize);

    onUnmounted(() => {
        cancelAnimationFrame(animationFrameId);
        window.removeEventListener('resize', handleResize);
        if (renderer) {
            renderer.dispose();
        }
    });
});
</script>

<template>
    <div ref="mainContainer" class="w-screen h-screen flex bg-white font-sans">
        <div class="w-1/2 h-full flex flex-col p-12 text-[#333]">
            <div class="flex items-center mb-8">
                <svg class="w-16 h-16 text-blue-500 mr-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l.7 4.4L18 7l-2.4 5.2L18 17l-5.3-.6L12 22l-.7-4.4L6 17l2.4-5.2L6 7l5.3.6z"/></svg>
                <h1 class="text-4xl font-bold">عيادة النور التخصصية</h1>
            </div>
            <p class="text-lg text-gray-600 mb-10 leading-relaxed">هذا النص هو مثال لنص يمكن أن يستبدل في نفس المساحة، حيث تم توليد هذا النص من مولد النصوص العربية، حيث يمكنك أن تولد مثل هذا النص أو العديد من النصوص الأخرى.</p>
            <h2 class="text-2xl font-semibold mb-4 border-b-2 border-blue-200 pb-2">خدماتنا الرئيسية</h2>
            <ul class="space-y-3 text-gray-700 text-lg list-disc list-inside mb-auto">
                <li>طب وتقويم الأسنان</li>
                <li>الأمراض الجلدية والتجميل</li>
                <li>قسم الأطفال وحديثي الولادة</li>
                <li>الطب الباطني وأمراض القلب</li>
            </ul>
        </div>
        <div ref="sceneContainer" class="w-1/2 h-full relative">
            <button @click="toggleFullscreen" class="absolute top-4 right-4 z-20 p-2 bg-black bg-opacity-50 rounded-full text-white hover:bg-opacity-75 transition-all" aria-label="Toggle fullscreen">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m0 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5h-4m0 0v-4m0 4l-5-5"></path></svg>
            </button>
            <div class="absolute top-0 left-0 w-full h-full border-4 border-black pointer-events-none z-10"></div>
        </div>
    </div>
</template>

<style>
body, html {
    margin: 0;
    padding: 0;
    overflow: hidden;
    font-family: 'Tajawal', sans-serif;
}
</style>