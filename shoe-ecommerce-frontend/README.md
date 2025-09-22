# Shoe E-commerce Frontend

A modern, responsive frontend customer portal for a shoe e-commerce platform built with React, TypeScript, and Tailwind CSS.

## Features

- Responsive design for all device sizes
- Product browsing and filtering
- Shopping cart functionality
- User authentication
- Order management
- Secure checkout process
- Product reviews and ratings

## Tech Stack

- **React**: UI library
- **TypeScript**: Type safety
- **Redux Toolkit**: State management
- **React Router**: Navigation
- **Axios**: API requests
- **Tailwind CSS**: Styling
- **Vite**: Build tool

## Prerequisites

- Node.js (v16 or higher)
- npm or yarn

## Installation

1. Clone the repository:

```bash
git clone <repository-url>
cd shoe-ecommerce-frontend
```

2. Install dependencies:

```bash
npm install
# or
yarn install
```

3. Create a `.env` file in the root directory with the following variables:

```
VITE_API_URL=http://localhost:8000/api
```

## Development

Start the development server:

```bash
npm run dev
# or
yarn dev
```

The application will be available at `http://localhost:3000`.

## Building for Production

Build the application for production:

```bash
npm run build
# or
yarn build
```

The build artifacts will be stored in the `dist/` directory.

## Preview Production Build

Preview the production build locally:

```bash
npm run preview
# or
yarn preview
```

## Project Structure

```
shoe-ecommerce-frontend/
├── public/             # Static assets
├── src/
│   ├── assets/         # Images, fonts, etc.
│   ├── components/     # Reusable components
│   ├── hooks/          # Custom React hooks
│   ├── pages/          # Page components
│   ├── services/       # API services
│   ├── store/          # Redux store and slices
│   ├── types/          # TypeScript type definitions
│   ├── utils/          # Utility functions
│   ├── App.tsx         # Main App component
│   ├── index.css       # Global styles
│   └── main.tsx        # Entry point
├── .env                # Environment variables
├── index.html          # HTML template
├── package.json        # Dependencies and scripts
├── tailwind.config.js  # Tailwind CSS configuration
├── tsconfig.json       # TypeScript configuration
└── vite.config.ts      # Vite configuration
```

## API Integration

The frontend communicates with the backend API using Axios. API service files are located in the `src/services` directory.

## Deployment

This application can be deployed to any static hosting service like Vercel, Netlify, or GitHub Pages.

## License

[ISC License](LICENSE) 