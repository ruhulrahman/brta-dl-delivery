export default {
  mutateCommonProperties ({ commit }, payload) {
    commit('mutateCommonProperties', payload)
  },
  isToggleNotification ({ commit }, payload) {
    commit('isToggleNotification', payload)
  },
  setList ({ commit }, payload) {
    commit('setList', payload)
  },
  resetList ({ commit }) {
    commit('resetList', [])
  },
  toggleStatus ({ commit }, payload) {
    commit('toggleStatus', payload)
  },
  toggleDropdownItemStatus ({ commit }, payload) {
    commit('toggleDropdownItemStatus', payload)
  }
}
