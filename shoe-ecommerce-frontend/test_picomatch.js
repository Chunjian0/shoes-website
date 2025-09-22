try {
  require('picomatch');
  console.log('picomatch loaded successfully!'); 
} catch (error) {
  console.error('Failed to load picomatch:', error);
} 