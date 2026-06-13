import api from './axios'

export const productsApi = {
  getAll:   (params) => api.get('/products', { params }),
  getStats: ()       => api.get('/products/stats'),
  create:   (data)   => api.post('/products', data),
  update:   (id, data) => api.put(`/products/${id}`, data),
  delete:   (id)     => api.delete(`/products/${id}`),
}