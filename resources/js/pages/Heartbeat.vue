<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { gsap } from 'gsap';

const svgContainer = ref(null);
let animation;

onMounted(() => {
  // Path data for the heartbeat line
  // يتكون من خط مستقيم، ثم نبضة، ثم خط مستقيم آخر
  const pathData = "M0 50 L100 50 L110 30 L125 70 L135 45 L145 50 L250 50";
  
  // Create the SVG elements dynamically inside the container
  const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  svg.setAttribute('viewBox', '0 0 250 100');
  svg.setAttribute('class', 'w-full h-auto');
  svg.style.overflow = 'visible'; // To allow glow to be seen

  // Create a filter for the glowing effect
  const defs = document.createElementNS("http://www.w3.org/2000/svg", "defs");
  const filter = document.createElementNS("http://www.w3.org/2000/svg", "filter");
  filter.setAttribute('id', 'glow');
  filter.innerHTML = `
    <feGaussianBlur stdDeviation="3.5" result="coloredBlur"></feGaussianBlur>
    <feMerge>
        <feMergeNode in="coloredBlur"></feMergeNode>
        <feMergeNode in="SourceGraphic"></feMergeNode>
    </feMerge>
  `;
  defs.appendChild(filter);
  svg.appendChild(defs);

  // The main path for the heartbeat
  const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
  path.setAttribute('d', pathData);
  path.setAttribute('fill', 'none');
  path.setAttribute('stroke', '#0d9488'); // Teal color to match your theme
  path.setAttribute('stroke-width', '2.5');
  path.setAttribute('stroke-linecap', 'round');
  path.setAttribute('stroke-linejoin', 'round');
  path.style.filter = 'url(#glow)';

  // A "mask" path that will reveal the main path
  const maskPath = path.cloneNode();
  maskPath.setAttribute('stroke', 'white'); // This color doesn't matter, it's a mask
  maskPath.setAttribute('id', 'mask-path');
  
  svg.appendChild(path);
  svg.appendChild(maskPath);
  
  if (svgContainer.value) {
      svgContainer.value.appendChild(svg);
  }

  const pathLength = maskPath.getTotalLength();
  
  // Set up the GSAP animation timeline
  animation = gsap.timeline({ repeat: -1, repeatDelay: 0.5 });
  
  animation
    // Animate the stroke-dasharray and stroke-dashoffset to "draw" the line
    .fromTo(maskPath, 
      { strokeDasharray: pathLength, strokeDashoffset: pathLength },
      { 
        strokeDashoffset: 0, 
        duration: 2.5, // The drawing speed
        ease: 'power1.inOut' 
      }
    )
    // Make the line pulse (the "beep" effect)
    .to(path, {
        strokeWidth: 4,
        duration: 0.08,
        ease: 'power2.out',
        repeat: 1,
        yoyo: true
    }, "-=1.5") // Start this pulse animation before the drawing is complete
    // Fade out the entire SVG to restart the loop smoothly
    .to(svg, {
        opacity: 0,
        duration: 0.7,
        ease: 'power1.in'
    })
    // Reset the properties for the next loop
    .set(maskPath, { strokeDashoffset: pathLength })
    .set(svg, { opacity: 1 });
});

onUnmounted(() => {
    // Clean up the animation to prevent memory leaks
    if (animation) {
        animation.kill();
    }
});
</script>

<template>
    <div class="w-full max-w-lg mx-auto text-center" ref="svgContainer">
        <h2 class="mt-4 text-4xl font-bold text-slate-700">أهلاً بكم في عيادتنا</h2>
        <p class="mt-2 text-xl text-slate-500">الرجاء اختيار طبيب من القائمة لعرض التفاصيل</p>
    </div>
</template>

<style scoped>
/* Optional: Add some container styling if needed */
.w-full {
    width: 100%;
}
.max-w-lg {
    max-width: 32rem; /* 512px */
}
</style>