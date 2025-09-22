/**
 * ImageService - 图片优化和处理服务
 * 
 * 这个服务提供图片优化、响应式图片加载和CDN支持功能，
 * 帮助减少图片加载时间，提高用户体验。
 */

interface ImageServiceConfig {
  // 基础URL配置
  baseUrl: string; // 图片服务器基础URL
  cdnUrl?: string; // CDN URL

  // 响应式尺寸配置
  breakpoints: number[]; // 响应式断点尺寸数组
  devicePixelRatios: number[]; // 设备像素比例数组

  // 图片格式配置
  supportWebP: boolean; // 是否支持WebP
  supportAvif: boolean; // 是否支持AVIF
  fallbackFormat: string; // 后备格式

  // 图片质量与加载配置
  defaultQuality: number; // 默认图片质量
  lowQualityPreview: boolean; // 是否使用低质量预览图
  placeholderBase64: boolean; // 是否使用Base64占位符
  placeholderColor: boolean; // 是否使用颜色占位符
  lazyLoadingDistance: number; // 懒加载距离（像素）

  // 缓存配置
  cacheBuster: string | null; // 缓存破坏参数
}

interface ImageOptions {
  width?: number; // 宽度（像素）
  height?: number; // 高度（像素）
  quality?: number; // 质量（0-100）
  crop?: boolean; // 是否裁剪
  format?: string; // 指定格式（webp/jpg/png/avif）
  blur?: number; // 模糊效果（0-100）
  brightness?: number; // 亮度（-100到100）
  autoWidth?: boolean; // 是否自动选择合适的宽度
  useCdn?: boolean; // 是否使用CDN
  lowQualityPreview?: boolean; // 低质量预览
  placeholder?: 'color' | 'lqip' | 'base64' | 'none'; // 占位符类型
  cacheBuster?: string; // 缓存破坏版本号
}

// 检测浏览器对图片格式的支持
const detectImageSupport = (): { webp: boolean; avif: boolean } => {
  let webp = false;
  let avif = false;

  // 检测WebP支持
  const webpCanvas = document.createElement('canvas');
  if (webpCanvas.getContext && webpCanvas.getContext('2d')) {
    webp = webpCanvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
  }

  // 尝试检测AVIF支持
  try {
    avif = document.createElement('img').src = 'data:image/avif;base64,AAAAIGZ0eXBhdmlmAAAAAGF2aWZtaWYxbWlhZk1BMUIAAADybWV0YQAAAAAAAAAoaGRscgAAAAAAAAAAcGljdAAAAAAAAAAAAAAAAGxpYmF2aWYAAAAADnBpdG0AAAAAAAEAAAAeaWxvYwAAAABEAAABAAEAAAABAAABGgAAAB0AAAAoaWluZgAAAAAAAQAAABppbmZlAgAAAAABAABhdjAxQ29sb3IAAAAAamlwcnAAAABLaXBjbwAAABRpc3BlAAAAAAAAAAIAAAACAAAAEHBpeGkAAAAAAwgICAAAAAxhdjFDgQ0MAAAAABNjb2xybmNseAACAAIAAYAAAAAXaXBtYQAAAAAAAAABAAEEAQKDBAAAACVtZGF0EgAKCBgANogQEAwgMg8f8D///8WfhwB8+ErK42A=',
      avif = true;
  } catch (e) {
    avif = false;
  }

  return { webp, avif };
};

class ImageService {
  private config: ImageServiceConfig;
  private imageFormatSupport: { webp: boolean; avif: boolean };
  private devicePixelRatio: number;
  private viewportWidth: number;
  
  private static instance: ImageService | null = null;

  constructor(config?: Partial<ImageServiceConfig>) {
    // 获取浏览器的图片格式支持能力
    this.imageFormatSupport = detectImageSupport();
    
    // 获取设备像素比
    this.devicePixelRatio = window.devicePixelRatio || 1;
    
    // 获取视口宽度
    this.viewportWidth = window.innerWidth;

    // 设置配置，合并默认值和自定义值
    this.config = {
      baseUrl: '',
      cdnUrl: '',
      breakpoints: [320, 640, 768, 1024, 1280, 1536, 1920],
      devicePixelRatios: [1, 2, 3],
      supportWebP: this.imageFormatSupport.webp,
      supportAvif: this.imageFormatSupport.avif,
      fallbackFormat: 'jpg',
      defaultQuality: 80,
      lowQualityPreview: true,
      placeholderBase64: true,
      placeholderColor: true,
      lazyLoadingDistance: 300,
      cacheBuster: null,
      ...config
    };

    // 监听窗口大小变化，更新视口宽度
    window.addEventListener('resize', this.handleResize);
  }

  /**
   * 获取单例实例
   */
  public static getInstance(config?: Partial<ImageServiceConfig>): ImageService {
    if (!ImageService.instance) {
      ImageService.instance = new ImageService(config);
    } else if (config) {
      // 更新配置
      ImageService.instance.updateConfig(config);
    }
    return ImageService.instance;
  }

  /**
   * 更新服务配置
   */
  public updateConfig(config: Partial<ImageServiceConfig>): void {
    this.config = {
      ...this.config,
      ...config
    };
  }

  /**
   * 处理窗口大小变化
   */
  private handleResize = (): void => {
    this.viewportWidth = window.innerWidth;
  };

  /**
   * 获取最佳响应式图片宽度
   */
  private getBestWidth(requestedWidth?: number): number {
    if (requestedWidth) {
      // 如果请求了特定宽度，找到大于等于该宽度的最小断点
      const deviceAdjustedWidth = requestedWidth * this.devicePixelRatio;
      
      for (const breakpoint of this.config.breakpoints) {
        if (breakpoint >= deviceAdjustedWidth) {
          return breakpoint;
        }
      }
      
      // 如果所有断点都小于请求的宽度，使用最大断点
      return Math.max(...this.config.breakpoints);
    } else {
      // 如果没有请求特定宽度，根据当前视口选择合适的断点
      for (const breakpoint of this.config.breakpoints) {
        if (breakpoint >= this.viewportWidth * this.devicePixelRatio) {
          return breakpoint;
        }
      }
      
      // 如果所有断点都小于当前视口宽度，使用最大断点
      return Math.max(...this.config.breakpoints);
    }
  }

  /**
   * 获取最佳图片格式
   */
  private getBestFormat(requestedFormat?: string): string {
    if (requestedFormat) {
      return requestedFormat;
    }
    
    if (this.config.supportAvif && this.imageFormatSupport.avif) {
      return 'avif';
    } else if (this.config.supportWebP && this.imageFormatSupport.webp) {
      return 'webp';
    }
    
    return this.config.fallbackFormat;
  }

  /**
   * 生成图片URL
   */
  public getImageUrl(
    imagePath: string,
    options: ImageOptions = {}
  ): string {
    if (!imagePath) {
      console.error('ImageService: Image path is required');
      return '';
    }

    // 处理URL协议
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      // 如果是完整URL，暂时不处理
      return imagePath;
    }

    // 确定基础URL
    const baseUrl = options.useCdn && this.config.cdnUrl 
      ? this.config.cdnUrl 
      : this.config.baseUrl;

    // 清理图片路径
    let cleanPath = imagePath;
    if (cleanPath.startsWith('/')) {
      cleanPath = cleanPath.substring(1);
    }

    // 构建查询参数
    const params = new URLSearchParams();

    // 处理尺寸
    const width = options.autoWidth 
      ? this.getBestWidth(options.width) 
      : options.width;
      
    if (width) {
      params.append('w', width.toString());
    }
    
    if (options.height) {
      params.append('h', options.height.toString());
    }

    // 处理裁剪
    if (options.crop) {
      params.append('fit', 'crop');
    }

    // 处理质量
    const quality = options.quality || this.config.defaultQuality;
    params.append('q', quality.toString());

    // 处理格式
    const format = this.getBestFormat(options.format);
    params.append('fm', format);

    // 处理特殊效果
    if (options.blur && options.blur > 0) {
      params.append('blur', options.blur.toString());
    }
    
    if (options.brightness !== undefined) {
      params.append('bri', options.brightness.toString());
    }

    // 处理缓存破坏
    const cacheBuster = options.cacheBuster || this.config.cacheBuster;
    if (cacheBuster) {
      params.append('v', cacheBuster);
    }

    // 构建最终URL
    const queryString = params.toString();
    return `${baseUrl}/${cleanPath}${queryString ? `?${queryString}` : ''}`;
  }

  /**
   * 获取图片的srcset属性（用于响应式图片）
   */
  public getSrcSet(
    imagePath: string,
    options: ImageOptions = {}
  ): string {
    if (!imagePath) {
      return '';
    }

    const srcSetEntries: string[] = [];
    const format = this.getBestFormat(options.format);

    // 为不同断点创建srcset
    for (const breakpoint of this.config.breakpoints) {
      const srcOptions = {
        ...options,
        width: breakpoint,
        format,
        autoWidth: false
      };
      
      const url = this.getImageUrl(imagePath, srcOptions);
      srcSetEntries.push(`${url} ${breakpoint}w`);
    }

    return srcSetEntries.join(', ');
  }

  /**
   * 获取图片的大小（用于响应式图片的sizes属性）
   */
  public getSizes(defaultSize: string = '100vw'): string {
    // 根据媒体查询设置不同的大小
    const sizesEntries: string[] = [
      '(max-width: 640px) 100vw',
      '(max-width: 768px) 75vw',
      '(max-width: 1024px) 60vw',
      defaultSize
    ];
    
    return sizesEntries.join(', ');
  }

  /**
   * 获取低质量图片预览URL
   */
  public getLowQualityPreview(
    imagePath: string,
    options: ImageOptions = {}
  ): string {
    return this.getImageUrl(imagePath, {
      ...options,
      width: 60,
      quality: 30,
      blur: 15
    });
  }

  /**
   * 获取Base64占位符
   */
  public getBase64Placeholder(color: string = '#f0f0f0'): string {
    // 生成1x1像素的占位符
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"><rect width="1" height="1" fill="${color}" /></svg>`;
    return `data:image/svg+xml;base64,${btoa(svg)}`;
  }

  /**
   * 获取颜色占位符
   */
  public getColorPlaceholder(color: string = '#f0f0f0'): string {
    return color;
  }

  /**
   * 初始化懒加载
   */
  public initLazyLoading(): void {
    // 如果浏览器支持IntersectionObserver，使用它进行懒加载
    if ('IntersectionObserver' in window) {
      const lazyImageObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const lazyImage = entry.target as HTMLImageElement;
            
            // 如果图片有data-src属性，将其设置为src
            if (lazyImage.dataset.src) {
              lazyImage.src = lazyImage.dataset.src;
            }
            
            // 如果图片有data-srcset属性，将其设置为srcset
            if (lazyImage.dataset.srcset) {
              lazyImage.srcset = lazyImage.dataset.srcset;
            }
            
            // 添加加载完成的类
            lazyImage.classList.add('loaded');
            
            // 图片加载完成后，停止观察
            lazyImageObserver.unobserve(lazyImage);
          }
        });
      }, {
        rootMargin: `${this.config.lazyLoadingDistance}px 0px`,
        threshold: 0.01
      });

      // 观察所有带有lazy-load类的图片
      document.querySelectorAll('img.lazy-load').forEach((img) => {
        lazyImageObserver.observe(img);
      });
    } else {
      // 如果浏览器不支持IntersectionObserver，使用滚动事件进行懒加载
      this.initLegacyLazyLoading();
    }
  }

  /**
   * 为不支持IntersectionObserver的浏览器初始化传统懒加载
   */
  private initLegacyLazyLoading(): void {
    let lazyImages = [].slice.call(document.querySelectorAll('img.lazy-load'));
    let active = false;

    const lazyLoad = () => {
      if (active === false) {
        active = true;

        setTimeout(() => {
          lazyImages.forEach((lazyImage: HTMLImageElement) => {
            const rect = lazyImage.getBoundingClientRect();
            if (
              rect.top <= window.innerHeight + this.config.lazyLoadingDistance &&
              rect.bottom >= -this.config.lazyLoadingDistance &&
              getComputedStyle(lazyImage).display !== 'none'
            ) {
              // 如果图片在视口范围内，加载图片
              if (lazyImage.dataset.src) {
                lazyImage.src = lazyImage.dataset.src;
              }
              
              if (lazyImage.dataset.srcset) {
                lazyImage.srcset = lazyImage.dataset.srcset;
              }
              
              lazyImage.classList.add('loaded');
              
              // 从懒加载数组中移除已加载的图片
              lazyImages = lazyImages.filter((img) => img !== lazyImage);

              // 如果所有图片都已加载，移除滚动事件监听
              if (lazyImages.length === 0) {
                document.removeEventListener('scroll', lazyLoad);
                window.removeEventListener('resize', lazyLoad);
                window.removeEventListener('orientationchange', lazyLoad);
              }
            }
          });

          active = false;
        }, 200);
      }
    };

    // 监听滚动事件
    document.addEventListener('scroll', lazyLoad);
    window.addEventListener('resize', lazyLoad);
    window.addEventListener('orientationchange', lazyLoad);
    
    // 初始加载
    lazyLoad();
  }

  /**
   * 清理资源
   */
  public dispose(): void {
    window.removeEventListener('resize', this.handleResize);
  }
}

// 创建并导出单例实例
export const imageService = ImageService.getInstance();

// 导出辅助函数
export const getImageUrl = (
  imagePath: string,
  options: ImageOptions = {}
): string => {
  return imageService.getImageUrl(imagePath, options);
};

export const getResponsiveImageProps = (
  imagePath: string,
  options: ImageOptions = {}
): {
  src: string;
  srcSet: string;
  sizes: string;
  loading?: 'lazy' | 'eager';
  placeholder?: string;
} => {
  return {
    src: imageService.getImageUrl(imagePath, options),
    srcSet: imageService.getSrcSet(imagePath, options),
    sizes: imageService.getSizes(),
    loading: 'lazy'
  };
};

// 图片组件Props接口（供React组件使用）
export interface ImageComponentProps {
  src: string;
  alt: string;
  width?: number;
  height?: number;
  className?: string;
  style?: React.CSSProperties;
  lazy?: boolean;
  quality?: number;
  format?: string;
  placeholder?: 'color' | 'lqip' | 'base64' | 'none';
  placeholderColor?: string;
  objectFit?: 'cover' | 'contain' | 'fill' | 'none' | 'scale-down';
  priority?: boolean;
  sizes?: string;
  onLoad?: () => void;
  onError?: () => void;
}

export default imageService; 