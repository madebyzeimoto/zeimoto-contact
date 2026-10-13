// ==========================================================
// Plugins :: zeimoto :: Contact :: Frontend
// ==========================================================
//
// Binds contact forms on first load and after navigation.
//
// ==========================================================

const observedForms = new WeakSet()

export const initializeContactForms = () => {
  document.querySelectorAll("[data-contact-form]").forEach((wrap) => {
    const form = wrap.querySelector("form")
    if (observedForms.has(form)) return
    observedForms.add(form)

    const submit = form.querySelector('[type="submit"]')
    const success = wrap.querySelector(".mds-contact-form-success")
    const globalError = form.querySelector("[data-global-error]")

    const clearErrors = () => {
      form.querySelectorAll("[data-field]").forEach((field) => {
        field.classList.remove("mds-contact-form-field--error")
        field.querySelector("input, textarea").removeAttribute("aria-invalid")
        field.querySelector("[data-field-error]").textContent = ""
      })
      globalError.textContent = ""
      globalError.hidden = true
    }

    const showErrors = (errors) => {
      Object.entries(errors).forEach(([key, message]) => {
        if (key === "global") {
          globalError.textContent = message
          globalError.hidden = false
          return
        }
        const field = form.querySelector(`[data-field="${key}"]`)
        if (field) {
          field.classList.add("mds-contact-form-field--error")
          field.querySelector("input, textarea").setAttribute("aria-invalid", "true")
          field.querySelector("[data-field-error]").textContent = message
        }
      })
    }

    form.addEventListener("submit", async (event) => {
      event.preventDefault()
      if (submit.disabled) return
      clearErrors()
      submit.disabled = true

      try {
        const response = await fetch(form.action, { method: "POST", body: new FormData(form) })
        const data = await response.json()

        if (data.success) {
          form.hidden = true
          success.hidden = false
        } else {
          showErrors(data.errors || { global: wrap.dataset.requestError })
          submit.disabled = false
        }
      } catch {
        showErrors({ global: wrap.dataset.requestError })
        submit.disabled = false
      }
    })
  })
}
