package pro.dite;

import com.sun.tools.javac.util.Pair;
import org.eclipse.jgit.revwalk.RevCommit;

import java.io.Serializable;
import java.util.*;

public class CacheEntry implements Serializable
{
    final HashSet<String> index = new HashSet<String>();

    final HashSet<String> removed = new HashSet<String>();
    final HashSet<String> added = new HashSet<String>();
    final HashSet<Rename> renamed = new HashSet<Rename>();
    final HashSet<String> changed = new HashSet<String>();

    String author;

    public CacheEntry(RevCommit commit)
    {
        this.author = commit.getAuthorIdent().getEmailAddress();
    }

    public ArrayList<String> getSortedRemovals()
    {
        ArrayList<String> sorted = new ArrayList<String>();
        sorted.addAll(this.removed);
        Collections.sort(sorted);
        return sorted;
    }

    public ArrayList<String> getSortedAdds()
    {
        ArrayList<String> sorted = new ArrayList<String>();
        sorted.addAll(this.added);
        Collections.sort(sorted);
        return sorted;
    }

    public ArrayList<String> getSortedChanges()
    {
        ArrayList<String> sorted = new ArrayList<String>();
        sorted.addAll(this.changed);
        Collections.sort(sorted);
        return sorted;
    }

    public ArrayList<Rename> getSortedRenames()
    {
        ArrayList<Rename> sorted = new ArrayList<Rename>();
        sorted.addAll(this.renamed);
        Collections.sort(sorted, new RenameComparator());
        return sorted;
    }

    private class RenameComparator implements Comparator<Rename>
    {
        @Override
        public int compare(Rename o1, Rename o2)
        {
            return o1.from.compareTo(o2.from);
        }
    }

    public void compile()
    {
        ArrayList<String> intersection = new ArrayList<String>();
        for (String rem : removed)
        {
            if (added.contains(rem))
            {
                intersection.add(rem);
            }
        }
        for (String inter : intersection)
        {
            changed.add(inter);
            added.remove(inter);
            removed.remove(inter);
        }
        added.remove("");
        removed.remove("");

        for (String def : index)
        {
            if (removed.contains(def) || added.contains(def) || def.equals(""))
            {
                continue;
            }
            boolean isRename = false;
            for (Rename ren : renamed)
            {
                if (ren.from.equals(def) || ren.to.equals(def))
                {
                    isRename = true;
                    break;
                }
            }
            if (!isRename)
            {
                changed.add(def);
            }
        }
    }
}
