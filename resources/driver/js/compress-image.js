/**
 * Клиентское сжатие изображений перед загрузкой (driver app).
 * Уменьшает размер файла и ускоряет отправку по мобильной сети.
 * @param {File} file - исходный файл (image/*)
 * @param {Object} options - { maxSize: number (px), quality: number (0-1) }
 * @returns {Promise<File>} сжатый файл (JPEG)
 */
export function compressImageFile(file, options = {}) {
  const maxSize = options.maxSize ?? 1920
  const quality = options.quality ?? 0.85

  return new Promise((resolve, reject) => {
    if (!file.type.startsWith('image/')) {
      resolve(file)
      return
    }
    const img = new Image()
    const url = URL.createObjectURL(file)
    img.onload = () => {
      URL.revokeObjectURL(url)
      let { width, height } = img
      if (width <= maxSize && height <= maxSize) {
        width = img.width
        height = img.height
      } else {
        if (width > height) {
          height = Math.round((height * maxSize) / width)
          width = maxSize
        } else {
          width = Math.round((width * maxSize) / height)
          height = maxSize
        }
      }
      const canvas = document.createElement('canvas')
      canvas.width = width
      canvas.height = height
      const ctx = canvas.getContext('2d')
      if (!ctx) {
        resolve(file)
        return
      }
      ctx.drawImage(img, 0, 0, width, height)
      canvas.toBlob(
        (blob) => {
          if (!blob) {
            resolve(file)
            return
          }
          const name = file.name.replace(/\.[^.]+$/i, '.jpg')
          resolve(new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() }))
        },
        'image/jpeg',
        quality
      )
    }
    img.onerror = () => {
      URL.revokeObjectURL(url)
      resolve(file)
    }
    img.src = url
  })
}

export default compressImageFile
