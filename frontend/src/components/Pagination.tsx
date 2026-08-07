import { ChevronLeft, ChevronRight } from 'lucide-react';
import type { PaginationMeta } from '@/types/models';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export function Pagination({
  page,
  meta,
  onPage,
  className,
}: {
  page: number;
  meta?: PaginationMeta;
  onPage: (page: number) => void;
  className?: string;
}) {
  if (!meta) return null;
  const hasNext = page < meta.last_page;

  return (
    <div className={cn('mt-4 flex items-center justify-between gap-2 text-sm', className)}>
      <p className="text-muted-foreground">
        {meta.total} result(s) · page {meta.current_page} of {meta.last_page}
      </p>
      <div className="flex gap-1">
        <Button
          size="sm"
          variant="outline"
          disabled={!meta.current_page || meta.current_page <= 1}
          onClick={() => onPage(page - 1)}
        >
          <ChevronLeft className="h-4 w-4" />
          Previous
        </Button>
        <Button size="sm" variant="outline" disabled={!hasNext} onClick={() => onPage(page + 1)}>
          Next
          <ChevronRight className="h-4 w-4" />
        </Button>
      </div>
    </div>
  );
}