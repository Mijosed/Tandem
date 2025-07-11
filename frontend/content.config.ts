import { defineCollection, defineContentConfig, z } from '@nuxt/content'



export default defineContentConfig({
  collections: {
    blog: defineCollection({
      type: 'page',
      source: 'blog/**/',
      schema: z.object({
        title: z.string(),
        description: z.string(),
        cover: z.string(),
        category: z.string(),
      })
    })
  }
})

