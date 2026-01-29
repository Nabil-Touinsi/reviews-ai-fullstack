// src/services/notify.js

const listeners = new Set();

/**
 * S'abonner aux notifications
 * @param {(payload: { type: 'success'|'error'|'info', message: string, duration?: number }) => void} cb
 * @returns {() => void} unsubscribe
 */
export function subscribeNotify(cb) {
  listeners.add(cb);
  return () => listeners.delete(cb);
}

function emit(payload) {
  for (const cb of listeners) cb(payload);
}

export function notifySuccess(message, duration = 3000) {
  emit({ type: "success", message, duration });
}

export function notifyError(message, duration = 3500) {
  emit({ type: "error", message, duration });
}

export function notifyInfo(message, duration = 3000) {
  emit({ type: "info", message, duration });
}
