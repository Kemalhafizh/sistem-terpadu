@props([
    'target' => 0,
    'duration' => 1500,
    'prefix' => '',
    'suffix' => '',
    'class' => 'text-2xl font-bold text-slate-800 dark:text-white',
])

<span {{ $attributes->merge(['class' => $class]) }}
      x-data="{ current: 0, target: {{ $target }}, duration: {{ $duration }} }"
      x-init="
          let start = null;
          function step(timestamp) {
              if (!start) start = timestamp;
              let progress = Math.min((timestamp - start) / duration, 1);
              current = Math.floor(progress * target);
              if (progress < 1) {
                  window.requestAnimationFrame(step);
              } else {
                  current = target;
              }
          }
          window.requestAnimationFrame(step);
      ">
    {{ $prefix }}<span x-text="current.toLocaleString()">{{ $target }}</span>{{ $suffix }}
</span>
