// Toggle sidebar mobile
document.getElementById('hamburger')
  ?.addEventListener('click', () => {
    document.getElementById('sidebar')
      .classList.toggle('-translate-x-full')
  })

// Confirmation suppression
document.querySelectorAll('[data-confirm]')
  .forEach(el => {
    el.addEventListener('click', function(e) {
      if (!confirm(this.dataset.confirm)) {
        e.preventDefault()
      }
    })
  })

// Fetch accessoires (formulaire emprunt)
const sel = document.getElementById('materiel_select')
if (sel) {
  sel.addEventListener('change', async function() {
    const id = this.value
    const wrap = document.getElementById('accessoires-wrap')
    if (!id) { wrap.innerHTML = ''; return }

    try {
      const res = await fetch(`/materiels/${id}/accessoires`)
      const data = await res.json()

      if (!data.length) {
        wrap.innerHTML = '<p class="text-gray-500 text-sm">Aucun accessoire compatible</p>'
        return
      }

      wrap.innerHTML = '<p class="text-xs font-medium text-gray-600 mb-2">Accessoires disponibles :</p>' +
        data.map(a => `
          <label class="flex items-center gap-2 text-sm ${!a.disponible ? 'opacity-50' : 'cursor-pointer'}">
            <input type="checkbox" name="accessoires[]"
              value="${a.id}"
              ${!a.disponible ? 'disabled' : ''}
              class="rounded border-gray-300 text-orange-500 focus:ring-orange-400">
            <span class="text-gray-700">${a.nom}</span>
            <span class="text-xs px-2 py-0.5 rounded-full ${a.disponible
              ? 'bg-green-100 text-green-800'
              : 'bg-red-100 text-red-800'}">
              ${a.disponible ? 'Disponible' : 'Indisponible'}
            </span>
          </label>`).join('')
    } catch (e) {
      wrap.innerHTML = ''
    }
  })
}
