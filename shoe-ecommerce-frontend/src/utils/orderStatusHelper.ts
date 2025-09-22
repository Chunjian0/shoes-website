import {
    parse, 
    parseISO, 
    isPast, 
    isValid, 
    differenceInDays 
} from 'date-fns';
import {
    ShoppingBagIcon, 
    CalendarDaysIcon, 
    TruckIcon, 
    CheckCircleIcon, 
    ClockIcon, 
    ExclamationCircleIcon,
    Cog8ToothIcon,
    GlobeAltIcon,
    MapPinIcon
} from '@heroicons/react/24/outline';
import React from 'react'; // Import React for ElementType

export const getDisplayStatus = (
    dbStatus: string,
    orderDateRaw: string | null,
    estimatedArrivalDateRaw: string | null
): { text: string; bgColor: string; textColor: string; icon: React.ElementType } => {

    const finalStatuses = ['delivered', 'completed', 'cancelled', 'refunded'];
    const normalizedDbStatus = dbStatus ? dbStatus.toLowerCase() : 'unknown';
    const baseStatusText = dbStatus ? dbStatus.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Unknown Status';

    // Define color pairs for better contrast
    const statusColors = {
        delivered: { bgColor: 'bg-green-100', textColor: 'text-green-800', icon: CheckCircleIcon },
        completed: { bgColor: 'bg-green-100', textColor: 'text-green-800', icon: CheckCircleIcon },
        cancelled: { bgColor: 'bg-red-100', textColor: 'text-red-800', icon: ExclamationCircleIcon },
        refunded: { bgColor: 'bg-red-100', textColor: 'text-red-800', icon: ExclamationCircleIcon },
        processing: { bgColor: 'bg-gray-100', textColor: 'text-gray-800', icon: Cog8ToothIcon }, // Updated gray text
        shipped: { bgColor: 'bg-blue-100', textColor: 'text-blue-800', icon: TruckIcon }, // Darker blue text
        in_transit: { bgColor: 'bg-purple-100', textColor: 'text-purple-800', icon: GlobeAltIcon }, // Darker purple text
        out_for_delivery: { bgColor: 'bg-cyan-100', textColor: 'text-cyan-800', icon: MapPinIcon }, // Darker cyan text
        pending: { bgColor: 'bg-yellow-100', textColor: 'text-yellow-800', icon: ClockIcon }, // Darker yellow text
        unknown: { bgColor: 'bg-gray-100', textColor: 'text-gray-800', icon: ShoppingBagIcon } // Default
    };

    // Final statuses from DB
    if (finalStatuses.includes(normalizedDbStatus)) {
        const statusKey = normalizedDbStatus as keyof typeof statusColors;
        // Return text and the defined color pair
        return { text: baseStatusText, ...statusColors[statusKey] }; 
    }

    // Simulation Logic
    if (orderDateRaw && estimatedArrivalDateRaw) {
        try {
            const orderDate = parseISO(orderDateRaw);
            const estimatedArrivalDate = parse(estimatedArrivalDateRaw, 'yyyy-MM-dd', new Date());
            const now = new Date();

            if (isValid(orderDate) && isValid(estimatedArrivalDate)) {
                if (isPast(estimatedArrivalDate) || now >= estimatedArrivalDate) {
                     if (normalizedDbStatus !== 'delivered' && normalizedDbStatus !== 'completed') {
                         // Return text and the 'delivered' color pair
                         return { text: `Delivered (Est.)`, ...statusColors.delivered }; 
                     } else {
                         // Return text and the 'delivered' color pair
                         return { text: baseStatusText, ...statusColors.delivered }; 
                     }
                }

                const totalDuration = differenceInDays(estimatedArrivalDate, orderDate);
                const elapsedDuration = differenceInDays(now, orderDate);
                
                if (totalDuration <= 0) {
                     // Return text and the 'out_for_delivery' color pair
                    return { text: "Out for Delivery", ...statusColors.out_for_delivery }; 
                }

                const progress = Math.max(0, Math.min(1, elapsedDuration / totalDuration)); 

                // Return text and the corresponding color pair based on progress
                if (progress <= 0.05) { 
                    return { text: "Processing", ...statusColors.processing }; 
                } else if (progress <= 0.3) {
                    return { text: "Shipped", ...statusColors.shipped }; 
                } else if (progress <= 0.8) {
                    return { text: "In Transit", ...statusColors.in_transit }; 
                } else { 
                    return { text: "Out for Delivery", ...statusColors.out_for_delivery }; 
                }
            }
        } catch (e) { console.error("Error calculating simulated status:", e); }
    }

    // Fallback to DB Status - Return text and the corresponding color pair
    const fallbackStatusKey = normalizedDbStatus as keyof typeof statusColors;
    if (statusColors[fallbackStatusKey]) {
         return { text: baseStatusText, ...statusColors[fallbackStatusKey] }; 
    } else {
        // Truly unknown status - Return text and the 'unknown' color pair
         return { text: baseStatusText, ...statusColors.unknown }; 
    }
};