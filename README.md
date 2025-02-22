# 📖 Draft Post Creator Plugin

## 📥 Installation
1. Download the plugin `.zip` file.
2. Go to your WordPress Dashboard → Plugins → Add New → Upload Plugin.
3. Upload the `.zip` file and click **Install Now**.
4. Activate the plugin.

## 🚀 Usage

### 🔗 API Endpoint
`POST /wp-json/draft-post-creator/v1/create-post`

### 🛡️ Authentication
- Use **Bearer Token** authentication.
- **Hardcoded Token:** `api_token_12345abcde67890`

### 📤 Request Headers
```json
{
  "Content-Type": "application/json",
  "Authorization": "Bearer api_token_12345abcde67890"
}
```

### 📋 Request Payload Example
```json
{
  "title": "Sample Draft Post",
  "description": "This is the main content of the draft post.",
  "meta_title": "SEO Optimized Title",
  "meta_keywords": "WordPress, Plugin, API",
  "meta_description": "This is a meta description for SEO purposes.",
  "img_url": "https://images.pexels.com/photos/30819809/pexels-photo-30819809.jpeg"
}
```

### ✅ Success Response
```json
{
  "success": "Post created successfully",
  "post_id": 123
}
```

### ❌ Error Responses
- **401 Unauthorized:** Missing or invalid Bearer Token.
- **400 Bad Request:** Missing required fields or invalid image URL.
- **500 Internal Server Error:** Post creation or image upload failed.

## 💡 Features
- 📝 Creates a draft post with a title, description, and SEO meta fields.
- 🖼️ Downloads and sets a featured image from a provided URL.
- 🔐 Secured with Bearer Token authentication.

## 💡 Notes
- The post is created in **Draft** status.
- The following SEO meta fields are saved using WordPress custom fields:
  - **Meta Title** (`meta_title`)
  - **Meta Keywords** (`meta_keywords`)
  - **Meta Description** (`meta_description`)
- The featured image is set using the provided `img_url`.