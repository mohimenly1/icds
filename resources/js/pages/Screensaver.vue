<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { gsap } from 'gsap';

const props = defineProps({
  images: {
    type: Array,
    required: true,
  }
});

const container = ref(null);
const image1 = ref(null);
const image2 = ref(null);

let currentIndex = 0;
let currentImageElement;
let nextImageElement;
let timeline;

// دالة لتطبيق تأثير كين بيرنز على الصورة
const applyKenBurns = (element) => {
  // تحديد اتجاهات الحركة والتقريب بشكل عشوائي
  const scale = 1.2 + Math.random() * 0.2; // Zoom between 1.2x and 1.4x
  const x = (Math.random() - 0.5) * 20; // Pan horizontally +/- 10%
  const y = (Math.random() - 0.5) * 20; // Pan vertically +/- 10%

  // استخدام GSAP لإنشاء الحركة
  gsap.fromTo(element, 
    { scale: 1, x: '0%', y: '0%' }, 
    { scale: scale, x: `${x}%`, y: `${y}%`, duration: 7, ease: 'linear' }
  );
};

// دالة لبدء عرض الشرائح
const startSlideshow = () => {
  if (!props.images || props.images.length === 0) return;

  // تحديد العناصر الحالية والتالية
  currentImageElement = image1.value;
  nextImageElement = image2.value;

  // تحميل الصورة الأولى
  currentImageElement.src = props.images[currentIndex];
  gsap.set(currentImageElement, { opacity: 1 });
  applyKenBurns(currentImageElement);

  // إعداد حلقة الانتقال
  timeline = gsap.timeline({
    repeat: -1, // تكرار لا نهائي
    delay: 5,   // الانتظار 5 ثوان قبل الانتقال الأول
    onRepeat: () => {
      // تحديث الصور
      currentIndex = (currentIndex + 1) % props.images.length;
      nextImageElement.src = props.images[currentIndex];

      // تطبيق تأثير الحركة على الصورة التالية
      applyKenBurns(nextImageElement);
      
      // تنفيذ تأثير التلاشي المتداخل
      gsap.to(currentImageElement, { opacity: 0, duration: 2, ease: 'power2.inOut' });
      gsap.to(nextImageElement, { opacity: 1, duration: 2, ease: 'power2.inOut' });

      // تبديل العناصر استعدادًا للحلقة التالية
      [currentImageElement, nextImageElement] = [nextImageElement, currentImageElement];
    }
  }).to({}, { duration: 7 }); // مدة كل شريحة (5 ثوان عرض + 2 ثانية انتقال)
};

onMounted(() => {
  // ملاحظة: تأكد من تثبيت GSAP في مشروعك
  // npm install gsap
  startSlideshow();
});

onUnmounted(() => {
  // إيقاف وتنظيف الرسوم المتحركة عند إزالة المكون
  if (timeline) {
    timeline.kill();
  }
  gsap.killTweensOf([image1.value, image2.value]);
});

</script>

<template>
    <div ref="container" class="w-full h-full bg-black relative overflow-hidden">
        <!-- عناصر الصور للتلاشي المتداخل -->
        <img ref="image1" class="absolute w-full h-full object-cover opacity-0" />
        <img ref="image2" class="absolute w-full h-full object-cover opacity-0" />

        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
        <div class="absolute bottom-0 left-0 w-full p-8 text-white text-center font-tajwal" dir="rtl">
            <h2 class="text-4xl font-bold [text-shadow:_0_2px_4px_rgb(0_0_0_/_50%)]">عيادتنا تهتم بصحتكم</h2>
            <p class="text-2xl mt-2 [text-shadow:_0_2px_4px_rgb(0_0_0_/_50%)]">للمزيد من المعلومات، يرجى لمس الشاشة</p>
        </div>
    </div>
</template>
