import {describe, it, expect} from "vitest";
import {  } from '@testing-library/react';
import {
  normalizeQualityForFormat,
} from '../../../features/photo-upload/lib/compressionProfiles';


describe("Checking returns parameters", () => {
    it("for png returns 100",() => {
        expect(normalizeQualityForFormat(10,"png")).toBe(100);
    });

    it("for jpeg/webp/avif returns 0-100",() => {
        const result = normalizeQualityForFormat(10,"jpeg");
        expect(result).toBeGreaterThanOrEqual(0);
        expect(result).toBeLessThanOrEqual(100);
    })
})