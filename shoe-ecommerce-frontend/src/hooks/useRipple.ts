import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

interface Ripple {
  key: number;
  x: number;
  y: number;
  size: number;
}

const useRipple = () => {
  const [ripples, setRipples] = useState<Ripple[]>([]);

  useEffect(() => {
    if (ripples.length > 0) {
      // Automatically clean up the oldest ripple after animation completes
      const timer = setTimeout(() => {
        setRipples(prevRipples => prevRipples.slice(1));
      }, 600); // Match animation duration

      return () => clearTimeout(timer);
    }
  }, [ripples]);

  const addRipple = (event: React.MouseEvent<HTMLElement>) => {
    const rippleContainer = event.currentTarget.getBoundingClientRect();
    const size = Math.max(rippleContainer.width, rippleContainer.height) * 2; // Ripple diameter
    const x = event.clientX - rippleContainer.left;
    const y = event.clientY - rippleContainer.top;

    const newRipple: Ripple = {
      key: Date.now(), // Simple unique key
      x,
      y,
      size,
    };

    setRipples(prevRipples => [...prevRipples, newRipple]);
  };

  const RippleContainer = () => {
    // Temporarily commented out the complex JSX to test esbuild parsing
    /*
    const rippleElements = ripples.map(({ key, x, y, size }) => {
      const spanProps = {
        key: key,
        initial: { scale: 0, opacity: 0.5 },
        animate: { scale: 1, opacity: 0 },
        exit: { opacity: 0 },
        transition: { duration: 0.6, ease: "easeOut" },
        style: {
          position: 'absolute' as const,
          left: x - size / 2,
          top: y - size / 2,
          width: size,
          height: size,
          borderRadius: '50%',
          backgroundColor: 'rgba(0, 0, 0, 0.1)',
          pointerEvents: 'none' as const,
          transformOrigin: 'center' as const,
          zIndex: 1,
        }
      };
      return <motion.span {...spanProps} />;
    });

    return (
      <AnimatePresence>
        {rippleElements}
      </AnimatePresence>
    );
    */
    return null; // Return null for testing
  };

  return { addRipple, RippleContainer };
};

export default useRipple; 