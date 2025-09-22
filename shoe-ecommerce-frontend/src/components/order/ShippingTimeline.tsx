import React from 'react';
import { format, parseISO, differenceInDays, addDays, isBefore, isAfter } from 'date-fns';
import {
    ClipboardDocumentCheckIcon,
    Cog8ToothIcon,
    TruckIcon,
    GlobeAltIcon,
    MapPinIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
} from '@heroicons/react/24/outline';
import { motion } from 'framer-motion';

interface ShippingTimelineProps {
    orderDateRaw: string | null;
    estimatedArrivalDateRaw: string | null;
    dbStatus: string | null; // e.g., 'pending', 'processing', 'shipped', 'delivered', 'cancelled'
}

const stages = [
    { name: 'Order Placed', icon: ClipboardDocumentCheckIcon, statusMatch: ['pending', 'processing', 'shipped', 'delivered', 'completed'] },
    { name: 'Processing', icon: Cog8ToothIcon, statusMatch: ['processing', 'shipped', 'delivered', 'completed'] },
    { name: 'Shipped', icon: TruckIcon, statusMatch: ['shipped', 'delivered', 'completed'] },
    { name: 'In Transit', icon: GlobeAltIcon, statusMatch: ['shipped', 'delivered', 'completed'] },
    { name: 'Out for Delivery', icon: MapPinIcon, statusMatch: ['shipped', 'delivered', 'completed'] },
    { name: 'Delivered', icon: CheckCircleIcon, statusMatch: ['delivered', 'completed'] }
];

// Map backend statuses to timeline indices more directly
const statusToIndexMap: { [key: string]: number } = {
    pending: 0,         // Order Placed
    processing: 1,      // Processing
    shipped: 2,         // Shipped (implies in transit until next status)
    delivered: 5,       // Delivered
    completed: 5,       // Completed (treat as delivered for timeline)
    cancelled: -1,      // Special case
    refunded: -1        // Special case
};


const ShippingTimeline: React.FC<ShippingTimelineProps> = ({
    orderDateRaw,
    estimatedArrivalDateRaw,
    dbStatus,
}) => {
    if (!orderDateRaw || !dbStatus) {
        return null; // Don't render if essential data is missing
    }

    const orderDate = parseISO(orderDateRaw);
    const estimatedArrivalDate = estimatedArrivalDateRaw ? parseISO(estimatedArrivalDateRaw) : addDays(orderDate, 5); // Default estimate
    const now = new Date();

    let currentStageIndex = statusToIndexMap[dbStatus.toLowerCase()] ?? 0; // Default to 'Order Placed' if status unknown

    // Refine current stage based on time progress IF the status isn't definitive (e.g., 'delivered' or 'cancelled')
    if (dbStatus.toLowerCase() !== 'delivered' && dbStatus.toLowerCase() !== 'completed' && dbStatus.toLowerCase() !== 'cancelled' && dbStatus.toLowerCase() !== 'refunded') {
        const totalDuration = differenceInDays(estimatedArrivalDate, orderDate);
        const elapsedDuration = differenceInDays(now, orderDate);
        const progress = totalDuration > 0 ? Math.max(0, Math.min(1, elapsedDuration / totalDuration)) : (isBefore(now, orderDate) ? 0 : 1);

        let calculatedIndex = 0;
        if (progress <= 0.01) calculatedIndex = 1; // Processing shortly after order
        else if (progress <= 0.3) calculatedIndex = 2; // Shipped around 30% mark
        else if (progress <= 0.8) calculatedIndex = 3; // In Transit / Shipped
        else if (progress < 1) calculatedIndex = 4; // Out for Delivery / Shipped
        else calculatedIndex = 5; // Expected delivery time passed

        // Use the greater of status-based index and time-based index, but cap at Shipped if backend says Processing etc.
        // Exception: If backend says shipped, time might push it to transit/delivery phases visually
        const maxIndexBasedOnStatus = statusToIndexMap[dbStatus.toLowerCase()] ?? 0;

        if (maxIndexBasedOnStatus < 2) { // If Pending or Processing
             currentStageIndex = Math.min(calculatedIndex, maxIndexBasedOnStatus + 1); // Can only guess one step ahead at most
             currentStageIndex = Math.max(currentStageIndex, maxIndexBasedOnStatus); // Ensure it's at least the status index
        } else if (maxIndexBasedOnStatus === 2) { // If Shipped
            currentStageIndex = Math.max(maxIndexBasedOnStatus, calculatedIndex); // Allow time calc to show transit/delivery phases
            currentStageIndex = Math.min(currentStageIndex, 4); // Don't show Delivered just based on time if status is Shipped
        } else {
             currentStageIndex = maxIndexBasedOnStatus; // Trust delivered/completed status
        }
    }


    const isCancelled = dbStatus.toLowerCase() === 'cancelled' || dbStatus.toLowerCase() === 'refunded';

    const getSimulatedDate = (index: number): string | null => {
         if (isCancelled || !orderDate || !estimatedArrivalDate || index === 0) return format(orderDate, 'yyyy-MM-dd HH:mm'); // Order date for first step
         if (index > currentStageIndex || (isCancelled && index > 0)) return null; // No date for future or post-cancel steps
         if (index === stages.length - 1 && (dbStatus.toLowerCase() === 'delivered' || dbStatus.toLowerCase() === 'completed')) return format(estimatedArrivalDate, 'yyyy-MM-dd HH:mm'); // Use estimate as delivery date for simulation


         const totalDuration = Math.max(1, differenceInDays(estimatedArrivalDate, orderDate)); // Avoid division by zero
         let progressFraction = 0;
         switch (index) {
             case 1: progressFraction = 0.05; break; // Processing
             case 2: progressFraction = 0.3; break; // Shipped
             case 3: progressFraction = 0.6; break; // In Transit
             case 4: progressFraction = 0.9; break; // Out for Delivery
             default: progressFraction = 0;
         }

         const simulatedDate = addDays(orderDate, Math.round(totalDuration * progressFraction));
         // Ensure simulated date doesn't exceed today unless it's the final 'delivered' stage
         if (isAfter(simulatedDate, now) && index < stages.length - 1) {
             return format(now, 'yyyy-MM-dd HH:mm'); // Cap at current time for intermediate steps
         }
         // Format with time for better precision if needed
         return format(simulatedDate, 'PPpp'); // Using 'PPpp' for date and time
     };


    return (
        <motion.div
            className="bg-white p-5 rounded-lg shadow-md border border-gray-200"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
        >
            <h2 className="text-xl font-semibold text-gray-700 mb-6 border-b pb-3">Shipment Tracking</h2>
            {isCancelled ? (
                 <div className="flex items-center text-red-600">
                     <XCircleIcon className="w-6 h-6 mr-2" />
                     <p className="font-medium">Order Cancelled</p>
                 </div>
            ) : (
                <ol className="relative border-l border-gray-200 ml-3">
                    {stages.map((stage, index) => {
                        const isCompleted = index < currentStageIndex;
                        const isCurrent = index === currentStageIndex;
                        const isFuture = index > currentStageIndex;
                        const simulatedDate = getSimulatedDate(index);

                        let IconComponent = stage.icon;
                        let iconColor = "text-gray-400";
                        let textColor = "text-gray-500";
                        let ringColor = "ring-gray-400";
                        let statusText = "";

                        if (isCompleted) {
                            IconComponent = CheckCircleIcon; // Use checkmark for completed steps
                            iconColor = "text-green-500";
                            textColor = "text-gray-900";
                            ringColor = "ring-green-500";
                            statusText = `Completed on ${simulatedDate}`;
                        } else if (isCurrent) {
                            IconComponent = stage.icon; // Use the stage's specific icon for current
                            iconColor = "text-blue-600 animate-pulse";
                            textColor = "text-blue-700 font-semibold";
                            ringColor = "ring-blue-600";
                            statusText = `Current Status${simulatedDate ? ' (Est. ' + simulatedDate + ')' : ''}`;
                        } else { // isFuture
                             IconComponent = ClockIcon; // Use clock for future steps
                             iconColor = "text-gray-400";
                             textColor = "text-gray-400";
                             ringColor = "ring-gray-400";
                             statusText = "Pending";
                        }


                        return (
                            <motion.li
                                key={stage.name}
                                className="mb-8 ml-6" // Increased margin-bottom
                                initial={{ opacity: 0, x: -20 }}
                                animate={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.3, delay: index * 0.1 }}
                            >
                                <span className={`absolute flex items-center justify-center w-6 h-6 bg-white rounded-full -left-3 ring-4 ${ringColor}`}>
                                    <IconComponent className={`w-4 h-4 ${iconColor}`} />
                                </span>
                                <h3 className={`text-base font-medium ${textColor}`}>{stage.name}</h3>
                                {simulatedDate && (isCompleted || isCurrent) && (
                                    <time className={`block text-xs font-normal leading-none ${isCurrent ? 'text-blue-600' : 'text-gray-500'} mt-1`}>
                                        {statusText}
                                    </time>
                                )}
                                 {!simulatedDate && isFuture && (
                                     <p className="text-xs text-gray-400 mt-1">{statusText}</p>
                                 )}
                            </motion.li>
                        );
                    })}
                </ol>
            )}
        </motion.div>
    );
};

export default ShippingTimeline; 