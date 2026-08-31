<div x-data="{
        message: '',
        success: true,
        show: false,
        timer: null,
        push(detail) {
            if (this.timer) { clearTimeout(this.timer); }
            this.message = detail.message;
            this.success = detail.success;
            this.show = true;
            this.timer = setTimeout(() => { this.show = false; }, detail.success ? 2500 : 4000);
        }
     }"
     x-on:menu-builder-toast.window="push($event.detail)"
     class="fixed top-4 right-4 z-50 pointer-events-none">
    <div x-show="show" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         :class="success ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800'"
         class="pointer-events-auto px-4 py-2.5 rounded-lg border shadow-soft-xl text-sm flex items-center gap-2 max-w-md">
        <span class="material-symbols-rounded text-[18px]" x-text="success ? 'check_circle' : 'error'"></span>
        <span x-text="message"></span>
    </div>
</div>
