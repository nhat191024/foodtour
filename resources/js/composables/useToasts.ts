import { ref, readonly } from 'vue'

const toastState = ref({
  show: false,
  message: '',
  type: 'success' as 'success' | 'error',
})

let timeoutId: number | undefined;

export function showToast(message: string, type: 'success' | 'error' = 'success') {
  if (timeoutId) {
    clearTimeout(timeoutId);
  }

  toastState.value = {
    show: true,
    message,
    type,
  }

  timeoutId = window.setTimeout(() => {
    hideToast();
  }, 6000) 
}

// Hàm mới để nút "X" có thể gọi
export function hideToast() {
  if (timeoutId) {
    clearTimeout(timeoutId);
  }
  toastState.value.show = false;
}

export function useToasts() {
  return {
    toast: readonly(toastState),
  }
}
