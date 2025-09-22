import { Link } from 'react-router-dom';

const NotFoundPage = () => {
  return (
    <div className="flex flex-col items-center justify-center py-16 px-4 text-center">
      <div className="text-6xl font-bold text-primary mb-4">404</div>
      <h1 className="text-2xl font-bold mb-4">Page Not Found</h1>
      <p className="text-neutral-600 mb-8 max-w-md">
        The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
      </p>
      <Link to="/" className="btn btn-primary">
        Return to Homepage
      </Link>
    </div>
  );
};

export default NotFoundPage; 